<?php

namespace Tests\Unit\BaseDatos;

use App\Helpers\SPHelper;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Throwable;

/**
 * Pruebas unitarias del cálculo del cobro: el procedimiento sp_calcular_tarifa.
 *
 * En Parkme! la regla de negocio central (cuánto paga un vehículo al salir) vive
 * en MySQL, no en PHP, así que la "unidad" bajo prueba es ese procedimiento. Las
 * pruebas lo ejecutan contra la base de datos de pruebas `parkme_test` (nunca la
 * de desarrollo), a través del mismo SPHelper que usa la aplicación.
 *
 * Aislamiento: cada prueba crea su empresa y sus tarifas dentro de una
 * transacción que se revierte al terminar, de modo que no queda ningún dato.
 *
 * Reglas del cobro que se verifican:
 *  - por_hora:     ⌈minutos / 60⌉ × valor, con mínimo 1 hora.
 *  - por_fraccion: ⌈minutos / fracción⌉ × valor, con mínimo 1 fracción.
 *  - plana_dia:    ⌈minutos / 1440⌉ × valor, con mínimo 1 día.
 *  - especial:     valor fijo si la hora de salida cae en la franja (puede cruzar la medianoche).
 *  - Prioridad:    tarifa del tipo de vehículo > tarifa genérica; especial > por_hora.
 *  - Sin tarifa activa: cobro 0 y nombre "Sin Tarifa".
 */
#[Group('bd')]
class CalculoTarifaTest extends TestCase
{
    private const AUTOMOVIL = 1;
    private const MOTOCICLETA = 2;

    private int $idEmpresa;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => env('DB_TEST_DATABASE', 'parkme_test'),
        ]);
        DB::purge('mysql');

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $this->markTestSkipped('La BD de pruebas MySQL (parkme_test) no está disponible: ' . $e->getMessage());
        }

        DB::beginTransaction();

        $empresa = SPHelper::ejecutar('sp_crear_empresa', [
            // NIT fijo (máx. 20 caracteres): la transacción se revierte, así que no choca con el índice único.
            'Parqueadero de Pruebas', 'parqueadero', 'Calle 1 # 2-3', '900123456-7', 'pruebas@parkme.test', '3000000000',
        ]);
        $this->assertNotEmpty($empresa, 'No se pudo crear la empresa de prueba en parkme_test.');
        $this->idEmpresa = (int) $empresa[0]->id_empresa;
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    // ── Ayudas ──────────────────────────────────────────────────────────────

    private function crearTarifa(string $tipo, float $valor, ?int $idTipoVehiculo = null, ?int $fraccion = null, ?string $inicio = null, ?string $fin = null): void
    {
        $resultado = SPHelper::ejecutar('sp_crear_tarifa', [
            $this->idEmpresa, $idTipoVehiculo, "Tarifa {$tipo}", $tipo, $valor, $fraccion, $inicio, $fin,
        ]);
        $this->assertNotNull($resultado, "No se pudo crear la tarifa {$tipo}.");
    }

    private function cobrar(string $ingreso, string $salida, int $idTipoVehiculo = self::AUTOMOVIL): object
    {
        $resultado = SPHelper::ejecutar('sp_calcular_tarifa', [$this->idEmpresa, $idTipoVehiculo, $ingreso, $salida]);
        $this->assertNotEmpty($resultado, 'sp_calcular_tarifa no devolvió resultado.');

        return $resultado[0];
    }

    // ── Pruebas ─────────────────────────────────────────────────────────────

    /**
     * UT-27 · Caso borde: valor mínimo (0 minutos).
     * Un vehículo que entra y sale en el mismo minuto paga la hora mínima, no $0.
     */
    #[TestDox('UT-27 Tarifa por hora: una estadía de 0 minutos cobra la hora mínima ($3.000)')]
    public function test_ut27_por_hora_cero_minutos_cobra_minimo_una_hora(): void
    {
        $this->crearTarifa('por_hora', 3000);

        $r = $this->cobrar('2026-11-20 08:00:00', '2026-11-20 08:00:00');

        $this->assertSame(0, (int) $r->minutos_transcurridos);
        $this->assertEquals(3000, (float) $r->total_cobrado);
    }

    /**
     * UT-28 · Caso límite: frontera exacta de la hora.
     * 60 minutos son 1 hora ($3.000); 61 minutos ya inician la segunda hora ($6.000).
     */
    #[TestDox('UT-28 Tarifa por hora: 60 min cobra 1 hora y 61 min cobra 2 horas (redondeo hacia arriba)')]
    public function test_ut28_por_hora_limite_exacto_de_la_hora(): void
    {
        $this->crearTarifa('por_hora', 3000);

        $sesenta = $this->cobrar('2026-11-20 08:00:00', '2026-11-20 09:00:00');
        $sesentaYUno = $this->cobrar('2026-11-20 08:00:00', '2026-11-20 09:01:00');

        $this->assertEquals(3000, (float) $sesenta->total_cobrado);
        $this->assertEquals(6000, (float) $sesentaYUno->total_cobrado);
    }

    /**
     * UT-29 · Caso de éxito (por fracción).
     * Fracción de 15 min a $800: 31 minutos son 3 fracciones (15 + 15 + 1) = $2.400.
     */
    #[TestDox('UT-29 Tarifa por fracción de 15 min: 31 minutos cobra 3 fracciones ($2.400)')]
    public function test_ut29_por_fraccion_redondea_a_la_fraccion_siguiente(): void
    {
        $this->crearTarifa('por_fraccion', 800, null, 15);

        $r = $this->cobrar('2026-11-20 08:00:00', '2026-11-20 08:31:00');

        $this->assertSame(31, (int) $r->minutos_transcurridos);
        $this->assertEquals(2400, (float) $r->total_cobrado);
    }

    /**
     * UT-30 · Caso de éxito (tarifa plana por día).
     * 25 horas (1.500 minutos) con tarifa diaria de $20.000 cobran 2 días = $40.000.
     */
    #[TestDox('UT-30 Tarifa plana por día: 25 horas cobra 2 días ($40.000)')]
    public function test_ut30_plana_dia_cobra_dias_iniciados(): void
    {
        $this->crearTarifa('plana_dia', 20000);

        $r = $this->cobrar('2026-11-20 08:00:00', '2026-11-21 09:00:00');

        $this->assertSame(1500, (int) $r->minutos_transcurridos);
        $this->assertEquals(40000, (float) $r->total_cobrado);
    }

    /**
     * UT-31 · Caso borde: franja especial que cruza la medianoche.
     * Tarifa nocturna 22:00–06:00 de $10.000 convive con la de por hora de $3.000.
     * Saliendo a las 02:30 (dentro de la franja) aplica la especial, que tiene prioridad.
     */
    #[TestDox('UT-31 Tarifa especial nocturna 22:00–06:00: salida a las 02:30 aplica la especial ($10.000)')]
    public function test_ut31_especial_nocturna_cruza_medianoche_y_tiene_prioridad(): void
    {
        $this->crearTarifa('por_hora', 3000);
        $this->crearTarifa('especial', 10000, null, null, '22:00:00', '06:00:00');

        $r = $this->cobrar('2026-11-20 23:00:00', '2026-11-21 02:30:00');

        $this->assertSame('Tarifa especial', $r->nombre_tarifa);
        $this->assertEquals(10000, (float) $r->total_cobrado);
    }

    /**
     * UT-32 · Caso límite: salida fuera de la franja especial.
     * Con las mismas tarifas que UT-31, salir a las 14:00 no está en 22:00–06:00,
     * así que aplica la tarifa por hora: 2 h 30 min → 3 horas = $9.000.
     */
    #[TestDox('UT-32 Fuera de la franja especial (salida 14:00) aplica la tarifa por hora ($9.000)')]
    public function test_ut32_fuera_de_franja_especial_aplica_por_hora(): void
    {
        $this->crearTarifa('por_hora', 3000);
        $this->crearTarifa('especial', 10000, null, null, '22:00:00', '06:00:00');

        $r = $this->cobrar('2026-11-20 11:30:00', '2026-11-20 14:00:00');

        $this->assertSame('Tarifa por_hora', $r->nombre_tarifa);
        $this->assertEquals(9000, (float) $r->total_cobrado);
    }

    /**
     * UT-33 · Regla de prioridad por tipo de vehículo.
     * Hay una tarifa genérica por hora ($3.000) y otra solo para motocicletas ($1.000).
     * En 2 horas: la moto paga $2.000 (su tarifa) y el automóvil $6.000 (la genérica).
     */
    #[TestDox('UT-33 La tarifa del tipo de vehículo prevalece sobre la genérica (moto $2.000 vs. auto $6.000)')]
    public function test_ut33_tarifa_del_tipo_de_vehiculo_prevalece(): void
    {
        $this->crearTarifa('por_hora', 3000);
        $this->crearTarifa('por_hora', 1000, self::MOTOCICLETA);

        $moto = $this->cobrar('2026-11-20 08:00:00', '2026-11-20 10:00:00', self::MOTOCICLETA);
        $auto = $this->cobrar('2026-11-20 08:00:00', '2026-11-20 10:00:00', self::AUTOMOVIL);

        $this->assertEquals(2000, (float) $moto->total_cobrado);
        $this->assertEquals(6000, (float) $auto->total_cobrado);
    }

    /**
     * UT-34 · Valores nulos (sin tarifa aplicable).
     * Si la única tarifa de la empresa está inactiva, no hay tarifa que aplicar:
     * id_tarifa es null, el nombre es "Sin Tarifa" y el cobro es $0.
     */
    #[TestDox('UT-34 Con la única tarifa inactiva: id_tarifa null, "Sin Tarifa" y cobro $0')]
    public function test_ut34_sin_tarifa_activa_cobra_cero(): void
    {
        $this->crearTarifa('por_hora', 3000);
        $tarifa = SPHelper::ejecutar('sp_listar_tarifas', [$this->idEmpresa, null])[0];
        SPHelper::ejecutar('sp_cambiar_estado_tarifa', [$tarifa->id_tarifa, 0]);

        $r = $this->cobrar('2026-11-20 08:00:00', '2026-11-20 12:00:00');

        $this->assertNull($r->id_tarifa);
        $this->assertSame('Sin Tarifa', $r->nombre_tarifa);
        $this->assertEquals(0, (float) $r->total_cobrado);
    }

    /**
     * UT-35 · Entrada inválida: hora de salida anterior a la de ingreso.
     * El procedimiento no debe producir un cobro negativo: reporta los minutos
     * negativos tal cual (-30) pero cobra el mínimo de la tarifa ($3.000).
     */
    #[TestDox('UT-35 Salida anterior al ingreso (-30 min): no cobra negativo, cobra el mínimo ($3.000)')]
    public function test_ut35_salida_anterior_al_ingreso_no_cobra_negativo(): void
    {
        $this->crearTarifa('por_hora', 3000);

        $r = $this->cobrar('2026-11-20 08:30:00', '2026-11-20 08:00:00');

        $this->assertSame(-30, (int) $r->minutos_transcurridos);
        $this->assertEquals(3000, (float) $r->total_cobrado);
        $this->assertGreaterThan(0, (float) $r->total_cobrado);
    }
}

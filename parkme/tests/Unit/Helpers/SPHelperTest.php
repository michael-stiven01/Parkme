<?php

namespace Tests\Unit\Helpers;

use App\Helpers\SPHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Pruebas unitarias de SPHelper: el único punto de entrada de la aplicación a la
 * base de datos. Por regla de arquitectura de Parkme!, toda lectura o escritura se
 * hace llamando un procedimiento almacenado a través de este helper.
 *
 * La base de datos se sustituye por un doble (mock del facade DB), así que estas
 * pruebas no necesitan MySQL.
 */
class SPHelperTest extends TestCase
{
    /**
     * UT-01 · Caso de éxito.
     * Con dos parámetros, el helper debe construir "CALL sp(?, ?)", pasar los
     * parámetros en el mismo orden y devolver las filas tal como llegan de la BD.
     */
    #[TestDox('UT-01 Construye el CALL con un placeholder por parámetro y devuelve las filas')]
    public function test_ut01_construye_call_con_placeholders_y_devuelve_filas(): void
    {
        $filas = [(object) ['id_tipo_vehiculo' => 1, 'NOMBRE' => 'Automóvil']];

        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_buscar_vehiculo_por_placa(?, ?)', [7, 'ABC123'])
            ->andReturn($filas);

        $resultado = SPHelper::ejecutar('sp_buscar_vehiculo_por_placa', [7, 'ABC123']);

        $this->assertSame($filas, $resultado);
    }

    /**
     * UT-02 · Caso borde: procedimiento sin parámetros.
     * Con el arreglo de parámetros vacío no debe quedar ningún "?" ni una coma
     * suelta: la sentencia esperada es exactamente "CALL sp()".
     */
    #[TestDox('UT-02 Sin parámetros genera "CALL sp()" sin placeholders')]
    public function test_ut02_sin_parametros_genera_call_vacio(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_listar_tipos_vehiculo()', [])
            ->andReturn([]);

        $resultado = SPHelper::ejecutar('sp_listar_tipos_vehiculo');

        $this->assertSame([], $resultado);
    }

    /**
     * UT-03 · Manejo de errores.
     * Si la BD lanza una excepción, el helper no la propaga: devuelve null y deja
     * constancia del fallo en la bitácora (sp_registrar_bitacora) con el nombre
     * del SP que falló y el mensaje original.
     */
    #[TestDox('UT-03 Ante una excepción de BD devuelve null y registra el fallo en la bitácora')]
    public function test_ut03_excepcion_de_bd_devuelve_null_y_registra_bitacora(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_crear_cliente(?)', ['x'])
            ->andThrow(new Exception('Duplicate entry'));

        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?)', [
                null,                                   // id_usuario (invitado)
                null,                                   // id_empresa
                'Ejecución Fallida de SP',
                'Base de Datos',
                'Error en sp_crear_cliente: Duplicate entry',
                '127.0.0.1',
            ])
            ->andReturn([]);

        $resultado = SPHelper::ejecutar('sp_crear_cliente', ['x']);

        $this->assertNull($resultado);
    }

    /**
     * UT-04 · Manejo de errores (caso borde de recursión).
     * Si el que falla es el propio sp_registrar_bitacora, el helper NO debe
     * intentar registrar el fallo en la bitácora otra vez (eso sería un ciclo
     * infinito). Se espera exactamente una llamada a la BD y un null.
     */
    #[TestDox('UT-04 Si falla sp_registrar_bitacora no reintenta registrar (evita el ciclo infinito)')]
    public function test_ut04_fallo_de_la_bitacora_no_genera_recursion(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->andThrow(new Exception('Tabla bitacora bloqueada'));

        $resultado = SPHelper::ejecutar('sp_registrar_bitacora', [null, null, 'a', 'b', 'c', 'd']);

        $this->assertNull($resultado);
    }
}

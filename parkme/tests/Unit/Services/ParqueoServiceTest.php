<?php

namespace Tests\Unit\Services;

use App\Mail\TicketIngreso;
use App\Mail\TicketSalida;
use App\Services\ParqueoService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Pruebas unitarias de ParqueoService: ingreso y salida de vehículos y envío de
 * los tickets por correo al cliente.
 *
 * Las reglas de validación del ingreso/salida (cupo disponible, vehículo ya
 * adentro, etc.) viven en los SP; aquí se prueba cómo el servicio interpreta sus
 * respuestas y cómo reacciona ante fallos. La BD y el correo son dobles.
 */
class ParqueoServiceTest extends TestCase
{
    private ParqueoService $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = new ParqueoService();
    }

    /**
     * UT-10 · Caso de éxito.
     * El ingreso llama a sp_ingreso_vehiculo con (empresa, vehículo, cupo, usuario)
     * en ese orden y devuelve la primera fila convertida en arreglo asociativo.
     */
    #[TestDox('UT-10 Registrar ingreso envía los 4 parámetros en orden y devuelve la fila del SP como arreglo')]
    public function test_ut10_registrar_ingreso_devuelve_fila_como_arreglo(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_ingreso_vehiculo(?, ?, ?, ?)', [1, 20, 5, 3])
            ->andReturn([(object) ['error' => null, 'id_registro' => 99, 'placa' => 'ABC123', 'codigo_cupo' => 'A-05']]);

        $resultado = $this->servicio->registrarIngreso([
            'id_empresa' => 1, 'id_vehiculo' => 20, 'id_cupo' => 5, 'id_usuario' => 3,
        ]);

        $this->assertIsArray($resultado);
        $this->assertNull($resultado['error']);
        $this->assertSame(99, $resultado['id_registro']);
        $this->assertSame('A-05', $resultado['codigo_cupo']);
    }

    /**
     * UT-11 · Valores nulos (fallo de la BD).
     * Si SPHelper devuelve null (la BD falló) el servicio no debe romperse:
     * devuelve el código de error controlado SP_SIN_RESPUESTA.
     */
    #[TestDox('UT-11 Registrar ingreso devuelve error SP_SIN_RESPUESTA cuando la BD no responde')]
    public function test_ut11_registrar_ingreso_sin_respuesta_devuelve_error_controlado(): void
    {
        DB::shouldReceive('select')->once()->with('CALL sp_ingreso_vehiculo(?, ?, ?, ?)', [1, 20, 5, 3])->andThrow(new Exception('Lost connection'));
        DB::shouldReceive('select')->once()->with('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?)', \Mockery::any())->andReturn([]);

        $resultado = $this->servicio->registrarIngreso([
            'id_empresa' => 1, 'id_vehiculo' => 20, 'id_cupo' => 5, 'id_usuario' => 3,
        ]);

        $this->assertSame(['error' => 'SP_SIN_RESPUESTA'], $resultado);
    }

    /**
     * UT-12 · Regla de negocio reportada por el SP.
     * Cuando el SP rechaza la salida (no hay registro activo para esa placa),
     * el servicio debe propagar el código de error del SP sin alterarlo, y
     * llamar al SP con el orden (empresa, placa, usuario).
     */
    #[TestDox('UT-12 Registrar salida propaga el error REGISTRO_ACTIVO_NO_ENCONTRADO del SP')]
    public function test_ut12_registrar_salida_propaga_error_del_sp(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_salida_vehiculo(?, ?, ?)', [1, 'XYZ999', 3])
            ->andReturn([(object) ['error' => 'REGISTRO_ACTIVO_NO_ENCONTRADO', 'id_registro' => null]]);

        $resultado = $this->servicio->registrarSalida('XYZ999', 1, 3);

        $this->assertSame('REGISTRO_ACTIVO_NO_ENCONTRADO', $resultado['error']);
        $this->assertNull($resultado['id_registro']);
    }

    /**
     * UT-13 · Valores nulos / vacíos.
     * Si el cliente no tiene correo (null o cadena vacía), el ticket de ingreso
     * no se envía, no se marca como enviado en la BD y queda una advertencia en el log.
     */
    #[TestDox('UT-13 Ticket de ingreso sin correo del cliente: no envía, no marca en BD y deja advertencia')]
    public function test_ut13_ticket_ingreso_sin_correo_no_envia_ni_marca(): void
    {
        Mail::fake();
        Log::spy();
        DB::shouldReceive('select')->never();

        $this->servicio->enviarTicketIngreso(['id_registro' => 99, 'correo_cliente' => '']);

        Mail::assertNothingSent();
        Log::shouldHaveReceived('warning')->once();
    }

    /**
     * UT-14 · Caso de éxito.
     * Con correo y número de registro, el ticket de salida se envía al cliente
     * y después se marca como enviado con sp_marcar_ticket_salida(id_registro).
     */
    #[TestDox('UT-14 Ticket de salida con correo: se envía al cliente y se marca como enviado en la BD')]
    public function test_ut14_ticket_salida_se_envia_y_se_marca(): void
    {
        Mail::fake();
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_marcar_ticket_salida(?)', [99])
            ->andReturn([]);

        $this->servicio->enviarTicketSalida([
            'id_registro' => 99, 'correo_cliente' => 'cliente@parkme.test', 'valor_cobrado' => 6000,
        ]);

        Mail::assertSent(TicketSalida::class, fn ($m) => $m->hasTo('cliente@parkme.test') && $m->datos['valor_cobrado'] === 6000);
    }

    /**
     * UT-15 · Caso borde.
     * Si el ticket tiene correo pero no trae id_registro, el correo se envía
     * igual, pero no hay registro que marcar: no se llama a la BD.
     */
    #[TestDox('UT-15 Ticket de ingreso sin id_registro: envía el correo pero no intenta marcarlo en la BD')]
    public function test_ut15_ticket_ingreso_sin_id_registro_envia_pero_no_marca(): void
    {
        Mail::fake();
        DB::shouldReceive('select')->never();

        $this->servicio->enviarTicketIngreso(['correo_cliente' => 'cliente@parkme.test']);

        Mail::assertSent(TicketIngreso::class, fn ($m) => $m->hasTo('cliente@parkme.test'));
    }

    /**
     * UT-16 · Manejo de excepciones.
     * Si el servidor de correo falla, la excepción NO debe propagarse (el ingreso
     * del vehículo ya quedó registrado y no puede revertirse por un correo):
     * se registra el error en el log y el ticket NO se marca como enviado.
     */
    #[TestDox('UT-16 Si el servidor de correo falla, captura la excepción, la registra y no marca el ticket')]
    public function test_ut16_fallo_del_correo_se_captura_y_no_marca_ticket(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new Exception('SMTP no disponible'));
        Log::spy();
        DB::shouldReceive('select')->never();

        $this->servicio->enviarTicketIngreso(['id_registro' => 99, 'correo_cliente' => 'cliente@parkme.test']);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn ($mensaje) => str_contains($mensaje, 'SMTP no disponible'));
    }

    /**
     * UT-37 · Valores nulos (fallo de la BD en la salida).
     * Si la BD falla al registrar la salida, el servicio devuelve el error
     * controlado SP_SIN_RESPUESTA en lugar de un cobro vacío o una excepción.
     */
    #[TestDox('UT-37 Registrar salida devuelve error SP_SIN_RESPUESTA cuando la BD no responde')]
    public function test_ut37_registrar_salida_sin_respuesta_devuelve_error_controlado(): void
    {
        DB::shouldReceive('select')->once()->with('CALL sp_salida_vehiculo(?, ?, ?)', [1, 'ABC123', 3])->andThrow(new Exception('Lost connection'));
        DB::shouldReceive('select')->once()->with('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?)', \Mockery::any())->andReturn([]);

        $this->assertSame(['error' => 'SP_SIN_RESPUESTA'], $this->servicio->registrarSalida('ABC123', 1, 3));
    }

    /**
     * UT-38 · Valores nulos + manejo de excepciones en el ticket de salida.
     * Sin correo (null) no se envía nada; con correo pero con el SMTP caído, la
     * excepción no se propaga (la salida y el cobro ya quedaron registrados) y el
     * ticket no se marca como enviado.
     */
    #[TestDox('UT-38 Ticket de salida: sin correo no envía, y si el SMTP falla no propaga el error ni marca el ticket')]
    public function test_ut38_ticket_salida_sin_correo_o_con_smtp_caido(): void
    {
        Log::spy();
        DB::shouldReceive('select')->never();
        Mail::shouldReceive('to')->once()->andThrow(new Exception('SMTP no disponible'));

        $this->servicio->enviarTicketSalida(['id_registro' => 99, 'correo_cliente' => null]);
        $this->servicio->enviarTicketSalida(['id_registro' => 99, 'correo_cliente' => 'cliente@parkme.test']);

        Log::shouldHaveReceived('warning')->once();
        Log::shouldHaveReceived('error')->once()->withArgs(fn ($m) => str_contains($m, 'ticket de salida'));
    }
}

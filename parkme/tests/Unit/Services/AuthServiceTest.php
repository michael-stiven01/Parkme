<?php

namespace Tests\Unit\Services;

use App\Mail\CodigoRecuperacion;
use App\Mail\CodigoVerificacion;
use App\Services\AuthService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Pruebas unitarias de AuthService: registro de usuarios, código de verificación
 * de correo y código de recuperación de contraseña.
 *
 * La BD se sustituye con un mock del facade DB y el envío de correo con Mail::fake(),
 * de modo que no se toca MySQL ni se envía ningún correo real.
 */
class AuthServiceTest extends TestCase
{
    private AuthService $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = new AuthService();
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * UT-05 · Caso de éxito (registro completo).
     * Al registrar un usuario se debe:
     *  - guardar la contraseña cifrada (nunca en texto plano),
     *  - generar un código de 6 caracteres alfanuméricos en mayúscula,
     *  - fijar su vencimiento exactamente 15 minutos después de "ahora",
     *  - enviar ese mismo código por correo al usuario.
     */
    #[TestDox('UT-05 Registra al usuario con contraseña cifrada, código de 6 caracteres que vence en 15 min y lo envía por correo')]
    public function test_ut05_registrar_usuario_cifra_genera_codigo_y_envia_correo(): void
    {
        Carbon::setTestNow('2026-11-23 10:00:00');
        $parametros = null;

        DB::shouldReceive('select')
            ->once()
            ->withArgs(function (string $query, array $params) use (&$parametros) {
                $parametros = $params;
                return $query === 'CALL sp_crear_usuario(?, ?, ?, ?, ?, ?)';
            })
            ->andReturn([]);

        $resultado = $this->servicio->registrarUsuario([
            'nombres'    => 'Ana',
            'apellidos'  => 'Gómez',
            'correo'     => 'ana@parkme.test',
            'contrasena' => 'Secreta#2026',
        ]);

        [$nombres, $apellidos, $correo, $hash, $codigo, $expira] = $parametros;

        $this->assertSame(['Ana', 'Gómez', 'ana@parkme.test'], [$nombres, $apellidos, $correo]);
        $this->assertNotSame('Secreta#2026', $hash, 'La contraseña no puede viajar en texto plano.');
        $this->assertTrue(Hash::check('Secreta#2026', $hash));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $codigo);
        $this->assertSame('2026-11-23 10:15:00', $expira);
        $this->assertSame(['correo' => 'ana@parkme.test', 'codigo' => $codigo], $resultado);

        Mail::assertSent(CodigoVerificacion::class, fn ($m) => $m->hasTo('ana@parkme.test') && $m->codigo === $codigo);
    }

    /**
     * UT-06 · Caso de éxito.
     * Cuando sp_verificar_codigo_email responde estado = 1, el código es válido
     * y el servicio debe devolver true.
     */
    #[TestDox('UT-06 Verificar código devuelve true cuando el SP responde estado = 1')]
    public function test_ut06_verificar_codigo_valido_devuelve_true(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_verificar_codigo_email(?, ?)', ['ana@parkme.test', 'A1B2C3'])
            ->andReturn([(object) ['estado' => 1, 'mensaje' => 'Correo verificado']]);

        $this->assertTrue($this->servicio->verificarCodigo('ana@parkme.test', 'A1B2C3'));
    }

    /**
     * UT-07 · Manejo de excepciones (código incorrecto o vencido).
     * Cuando el SP responde estado = 0, el servicio debe lanzar una Exception
     * cuyo mensaje es exactamente el que devolvió el SP, para mostrarlo al usuario.
     */
    #[TestDox('UT-07 Verificar código lanza excepción con el mensaje del SP cuando estado = 0')]
    public function test_ut07_verificar_codigo_invalido_lanza_excepcion_con_mensaje_del_sp(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->andReturn([(object) ['estado' => 0, 'mensaje' => 'El código ha expirado']]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El código ha expirado');

        $this->servicio->verificarCodigo('ana@parkme.test', 'ZZZZZZ');
    }

    /**
     * UT-08 · Valores nulos / vacíos.
     * Si el SP no devuelve filas (o SPHelper devuelve null por un fallo de BD),
     * el servicio no puede asumir que el código es válido: debe lanzar
     * "Error al verificar el código.".
     */
    #[TestDox('UT-08 Verificar código lanza "Error al verificar el código." si el SP no devuelve filas')]
    public function test_ut08_verificar_codigo_sin_respuesta_lanza_excepcion(): void
    {
        DB::shouldReceive('select')->once()->andReturn([]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error al verificar el código.');

        $this->servicio->verificarCodigo('ana@parkme.test', '');
    }

    /**
     * UT-09 · Caso de éxito.
     * El código de recuperación de contraseña debe enviarse con el Mailable
     * CodigoRecuperacion (no con el de verificación) al correo indicado.
     */
    #[TestDox('UT-09 Envía el código de recuperación con el correo CodigoRecuperacion al destinatario correcto')]
    public function test_ut09_envia_codigo_de_recuperacion(): void
    {
        $this->servicio->enviarCodigoRecuperacion('ana@parkme.test', 'R3C0V3');

        Mail::assertSent(CodigoRecuperacion::class, fn ($m) => $m->hasTo('ana@parkme.test') && $m->codigo === 'R3C0V3');
        Mail::assertNotSent(CodigoVerificacion::class);
    }

    /**
     * UT-36 · Manejo de excepciones (regresión de un defecto encontrado al escribir esta suite).
     * Si sp_crear_usuario falla (BD caída, correo duplicado por una carrera, etc.),
     * SPHelper devuelve null. Antes, el servicio ignoraba ese null: enviaba el
     * código de verificación y reportaba el registro como exitoso aunque la cuenta
     * nunca se hubiera creado. Ahora debe lanzar una excepción y NO enviar correo.
     */
    #[TestDox('UT-36 Si sp_crear_usuario falla, el registro lanza excepción y no envía el código de verificación')]
    public function test_ut36_registro_con_fallo_de_bd_lanza_excepcion_y_no_envia_correo(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_crear_usuario(?, ?, ?, ?, ?, ?)', \Mockery::any())
            ->andThrow(new Exception("Duplicate entry 'ana@parkme.test' for key 'usuarios_correo_unique'"));
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?)', \Mockery::any())
            ->andReturn([]);

        $excepcion = null;
        try {
            $this->servicio->registrarUsuario([
                'nombres' => 'Ana', 'apellidos' => 'Gómez', 'correo' => 'ana@parkme.test', 'contrasena' => 'Secreta#2026',
            ]);
        } catch (Exception $e) {
            $excepcion = $e;
        }

        $this->assertNotNull($excepcion, 'Se esperaba una excepción al fallar sp_crear_usuario.');
        $this->assertSame('No se pudo guardar el usuario. Intenta de nuevo en unos minutos.', $excepcion->getMessage());
        Mail::assertNothingSent();
    }
}

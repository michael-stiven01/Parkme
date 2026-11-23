<?php

namespace Tests\Unit\Services;

use App\Services\BitacoraService;
use App\Services\SesionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Pruebas unitarias de BitacoraService (auditoría de acciones) y SesionService
 * (inicio, consulta y cierre de la sesión del usuario).
 */
class BitacoraYSesionServiceTest extends TestCase
{
    /**
     * UT-17 · Valores nulos.
     * Una acción del sistema sin usuario ni detalle (por ejemplo, un fallo antes
     * de iniciar sesión) debe registrarse igual: en el log aparece "N/A" y al SP
     * le llegan los null reales, junto con la IP de la petición.
     */
    #[TestDox('UT-17 Bitácora con usuario y detalle nulos: escribe "N/A" en el log y envía null al SP con la IP')]
    public function test_ut17_bitacora_con_nulos_registra_na_y_envia_null(): void
    {
        Log::spy();
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?)', [
                null, null, 'Salida de vehículo registrada', 'Parqueo', null, '127.0.0.1',
            ])
            ->andReturn([]);

        BitacoraService::registrar('Salida de vehículo registrada', 'Parqueo');

        Log::shouldHaveReceived('info')->withArgs(
            fn ($m) => $m === 'Bitácora - Módulo: Parqueo | Acción: Salida de vehículo registrada | Usuario ID: N/A | Detalle: N/A'
        );
    }

    /**
     * UT-18 · Caso de éxito + seguridad.
     * Al iniciar sesión con los datos que devuelve sp_verificar_usuario, el
     * usuario queda autenticado y usuarioActual() devuelve sus datos SIN el
     * hash de la contraseña (CONTRASENA es un campo oculto del modelo).
     */
    #[TestDox('UT-18 Iniciar sesión autentica al usuario y usuarioActual() no expone la contraseña')]
    public function test_ut18_iniciar_sesion_autentica_y_oculta_contrasena(): void
    {
        $sesion = new SesionService();

        $this->assertFalse($sesion->estaAutenticado());
        $this->assertNull($sesion->usuarioActual(), 'Sin sesión no debe haber usuario actual.');

        $sesion->iniciarSesion([
            'id_usuario' => 3, 'id_empresa' => 1, 'NOMBRES' => 'Ana', 'CORREO' => 'ana@parkme.test',
            'CONTRASENA' => '$2y$04$hashDePrueba', 'ROL' => 'administrador', 'CORREO_VERIFICADO' => 1,
        ]);

        $usuario = $sesion->usuarioActual();

        $this->assertTrue($sesion->estaAutenticado());
        $this->assertSame(3, $usuario['id_usuario']);
        $this->assertSame('administrador', $usuario['ROL']);
        $this->assertArrayNotHasKey('CONTRASENA', $usuario);
    }

    /**
     * UT-19 · Caso de éxito.
     * Cerrar sesión deja al usuario como invitado y regenera el token CSRF, para
     * que un formulario abierto antes del cierre no pueda reutilizarse.
     */
    #[TestDox('UT-19 Cerrar sesión deja al usuario como invitado y regenera el token CSRF')]
    public function test_ut19_cerrar_sesion_desautentica_y_regenera_token(): void
    {
        $this->app['session']->start();
        $sesion = new SesionService();
        $sesion->iniciarSesion(['id_usuario' => 3, 'CORREO' => 'ana@parkme.test']);
        $tokenAnterior = $this->app['session']->token();

        $sesion->cerrarSesion();

        $this->assertFalse(Auth::check());
        $this->assertNotSame($tokenAnterior, $this->app['session']->token());
    }
}

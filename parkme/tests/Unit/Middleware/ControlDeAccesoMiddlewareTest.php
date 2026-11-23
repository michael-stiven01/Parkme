<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AutenticacionMiddleware;
use App\Http\Middleware\SoloInvitadosMiddleware;
use App\Services\SesionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Mockery;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Pruebas unitarias del control de acceso. Las reglas de negocio son:
 *  1. Sin sesión, ninguna pantalla interna es accesible (va al login).
 *  2. Con sesión pero con el correo sin verificar, solo se permite la pantalla de verificación.
 *  3. Con correo verificado pero sin empresa registrada, se obliga a registrar la empresa.
 *  4. Con todo en regla, se deja pasar.
 *  5. Un usuario con sesión no debe ver las pantallas de invitado (login, registro).
 *
 * SesionService se reemplaza por un mock para fijar el estado del usuario.
 */
class ControlDeAccesoMiddlewareTest extends TestCase
{
    /** Crea una petición asociada a la ruta con el nombre indicado. */
    private function peticionA(string $nombreRuta): Request
    {
        $request = Request::create('/ruta-de-prueba');
        $ruta = (new Route('GET', 'ruta-de-prueba', []))->name($nombreRuta);
        $ruta->bind($request);
        $request->setRouteResolver(fn () => $ruta);

        return $request;
    }

    /** Middleware con un SesionService simulado en el estado dado. */
    private function middleware(string $clase, bool $autenticado, ?array $usuario = null)
    {
        $sesion = Mockery::mock(SesionService::class);
        $sesion->shouldReceive('estaAutenticado')->andReturn($autenticado);
        $sesion->shouldReceive('usuarioActual')->andReturn($usuario);

        return new $clase($sesion);
    }

    private function siguiente(): \Closure
    {
        return fn () => new Response('contenido protegido', 200);
    }

    /**
     * UT-20 · Valores nulos (sin sesión).
     * Un visitante sin sesión que pide el dashboard es redirigido al login.
     */
    #[TestDox('UT-20 Sin sesión, una ruta protegida redirige al login')]
    public function test_ut20_sin_sesion_redirige_al_login(): void
    {
        $mw = $this->middleware(AutenticacionMiddleware::class, false);

        $respuesta = $mw->handle($this->peticionA('dashboard'), $this->siguiente());

        $this->assertTrue($respuesta->isRedirect(route('login')));
    }

    /**
     * UT-21 · Regla de negocio + caso borde (evitar bucle de redirección).
     * Con el correo sin verificar se redirige a la verificación; pero si la
     * petición YA es la pantalla de verificación, se deja pasar (de lo contrario
     * el navegador entraría en un bucle infinito de redirecciones).
     */
    #[TestDox('UT-21 Correo sin verificar: redirige a verificación, salvo si ya está en esa pantalla')]
    public function test_ut21_correo_sin_verificar_redirige_y_evita_bucle(): void
    {
        $usuario = ['CORREO_VERIFICADO' => 0, 'id_empresa' => 1];
        $mw = $this->middleware(AutenticacionMiddleware::class, true, $usuario);

        $haciaDashboard = $mw->handle($this->peticionA('dashboard'), $this->siguiente());
        $enVerificacion = $mw->handle($this->peticionA('verification.notice'), $this->siguiente());

        $this->assertTrue($haciaDashboard->isRedirect(route('verification.notice')));
        $this->assertSame(200, $enVerificacion->getStatusCode());
    }

    /**
     * UT-22 · Valores nulos/vacíos (empresa no registrada).
     * Un usuario verificado con id_empresa null debe registrar su empresa antes
     * de usar el sistema; si ya está en esa pantalla, se le deja pasar.
     */
    #[TestDox('UT-22 Usuario verificado sin empresa (id_empresa null) es obligado a registrar la empresa')]
    public function test_ut22_usuario_sin_empresa_redirige_a_registrar_empresa(): void
    {
        $usuario = ['CORREO_VERIFICADO' => 1, 'id_empresa' => null];
        $mw = $this->middleware(AutenticacionMiddleware::class, true, $usuario);

        $haciaClientes = $mw->handle($this->peticionA('clientes'), $this->siguiente());
        $enRegistro = $mw->handle($this->peticionA('empresa.registrar'), $this->siguiente());

        $this->assertTrue($haciaClientes->isRedirect(route('empresa.registrar')));
        $this->assertSame(200, $enRegistro->getStatusCode());
    }

    /**
     * UT-23 · Caso de éxito.
     * Usuario autenticado, verificado y con empresa: accede al contenido.
     */
    #[TestDox('UT-23 Usuario autenticado, verificado y con empresa accede a la ruta protegida')]
    public function test_ut23_usuario_completo_accede(): void
    {
        $usuario = ['CORREO_VERIFICADO' => 1, 'id_empresa' => 1];
        $mw = $this->middleware(AutenticacionMiddleware::class, true, $usuario);

        $respuesta = $mw->handle($this->peticionA('dashboard'), $this->siguiente());

        $this->assertSame(200, $respuesta->getStatusCode());
        $this->assertSame('contenido protegido', $respuesta->getContent());
    }

    /**
     * UT-24 · Regla de negocio (solo invitados).
     * Un usuario con sesión que intenta abrir el login es enviado al dashboard;
     * un invitado sí puede ver el login.
     */
    #[TestDox('UT-24 Las pantallas de invitado envían al dashboard a quien ya tiene sesión')]
    public function test_ut24_solo_invitados_redirige_al_dashboard_si_hay_sesion(): void
    {
        $conSesion = $this->middleware(SoloInvitadosMiddleware::class, true);
        $sinSesion = $this->middleware(SoloInvitadosMiddleware::class, false);

        $this->assertTrue($conSesion->handle($this->peticionA('login'), $this->siguiente())->isRedirect(route('dashboard')));
        $this->assertSame(200, $sinSesion->handle($this->peticionA('login'), $this->siguiente())->getStatusCode());
    }
}

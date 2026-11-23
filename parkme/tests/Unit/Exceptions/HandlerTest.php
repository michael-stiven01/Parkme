<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * Pruebas unitarias del manejador global de excepciones. La regla es:
 * los errores HTTP conocidos (404, 403…) los maneja Laravel; cualquier otro
 * error se registra en el log y el usuario ve una página 500 genérica, sin
 * detalles internos.
 */
class HandlerTest extends TestCase
{
    /**
     * UT-25 · Manejo de errores (registro).
     * Un error HTTP (404) no se registra como error; una excepción no controlada
     * sí, con el mensaje y el contexto de la petición (URL y método).
     */
    #[TestDox('UT-25 Registra en el log las excepciones no controladas e ignora los errores HTTP (404)')]
    public function test_ut25_report_registra_solo_excepciones_no_http(): void
    {
        Log::spy();

        Handler::report(new NotFoundHttpException('No existe'));
        Log::shouldNotHaveReceived('error');

        Handler::report(new RuntimeException('División por cero en el cobro'));
        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn ($mensaje, $contexto) =>
                $mensaje === 'Excepción no controlada: División por cero en el cobro'
                && isset($contexto['url'], $contexto['method'], $contexto['timestamp'])
                && $contexto['user_id'] === 'invitado');
    }

    /**
     * UT-26 · Manejo de errores (respuesta al usuario).
     * Ante una excepción no controlada se responde 500 con la vista genérica y
     * sin el mensaje interno; para un error HTTP se devuelve null para que
     * Laravel muestre su página estándar.
     */
    #[TestDox('UT-26 Responde 500 genérico sin exponer el detalle interno, y delega los errores HTTP a Laravel')]
    public function test_ut26_render_oculta_detalle_y_delega_errores_http(): void
    {
        $request = Request::create('/parqueo/salida');

        $respuesta = Handler::render(new RuntimeException('SQLSTATE[HY000] contraseña=secreta'), $request);

        $this->assertSame(500, $respuesta->getStatusCode());
        $this->assertStringNotContainsString('SQLSTATE', $respuesta->getContent());
        $this->assertNull(Handler::render(new NotFoundHttpException(), $request));
    }
}

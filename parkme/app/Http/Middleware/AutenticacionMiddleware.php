<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SesionService;
use Illuminate\Support\Facades\Auth;

class AutenticacionMiddleware
{
    protected SesionService $sesionService;

    public function __construct(SesionService $sesionService)
    {
        $this->sesionService = $sesionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->sesionService->estaAutenticado()) {
            // Not authenticated at all, kick to login
            return redirect()->route('login');
        }

        $usuario = $this->sesionService->usuarioActual();

        // 1. Check if email is verified
        if (!(bool)$usuario['CORREO_VERIFICADO']) {
            // Prevent redirect loop
            if (!$request->routeIs('verification.notice')) {
                return redirect()->route('verification.notice');
            }
        } else {
            // 2. Check if company is registered
            if (empty($usuario['id_empresa'])) {
                // Prevent redirect loop
                if (!$request->routeIs('empresa.registrar') && !$request->routeIs('cerrar-sesion')) {
                    return redirect()->route('empresa.registrar');
                }
            }
        }

        return $next($request);
    }
}

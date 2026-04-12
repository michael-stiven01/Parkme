<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SesionService;

class SoloInvitadosMiddleware
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
        if ($this->sesionService->estaAutenticado()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}

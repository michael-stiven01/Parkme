<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler
{
    /**
     * Report or log an exception.
     *
     * @param Throwable $e
     * @return void
     */
    public static function report(Throwable $e): void
    {
        // Don't log normal HTTP exceptions (like 404) at error level unless needed,
        // but since requirement is "all unhandled exceptions", we log them.
        // Actually, we skip HttpExceptionInterface to avoid polluting log with 404s,
        // or we just log them as warning. For "unhandled", we generally mean 500s.
        if ($e instanceof HttpExceptionInterface) {
            return; // Laravel handles these normally without breaking the app
        }

        $context = [
            'timestamp' => now()->toDateTimeString(),
        ];

        if (request()) {
            $context['url'] = request()->fullUrl();
            $context['method'] = request()->method();
            $context['user_id'] = auth()->check() ? auth()->id() : 'invitado';
        }

        Log::error('Excepción no controlada: ' . $e->getMessage(), $context);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Throwable $e
     * @param Request $request
     * @return Response|null
     */
    public static function render(Throwable $e, Request $request): ?Response
    {
        if (App::isDownForMaintenance()) {
            return null;
        }

        // Let Laravel gracefully handle known HTTP exceptions (e.g. abort(404), abort(403))
        if ($e instanceof HttpExceptionInterface) {
            return null;
        }

        // Return a custom 500 error view and never expose internal details
        return response()->view('errors.500', [], 500);
    }
}

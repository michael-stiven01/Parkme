<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.custom' => \App\Http\Middleware\AutenticacionMiddleware::class,
            'guest.custom' => \App\Http\Middleware\SoloInvitadosMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            \App\Exceptions\Handler::report($e);
        });

        $exceptions->render(function (Throwable $e, \Illuminate\Http\Request $request) {
            return \App\Exceptions\Handler::render($e, $request);
        });
    })->create();

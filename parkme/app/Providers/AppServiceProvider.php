<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!app()->runningInConsole()) {
            try {
                DB::connection()->getPdo();
                Log::info('Conexión a la base de datos establecida correctamente al iniciar la aplicación.');
            } catch (\Exception $e) {
                die('Error crítico: No se pudo conectar a la base de datos. Verifique la configuración.');
            }
        }
    }
}

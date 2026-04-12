<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BitacoraService
{
    /**
     * Registra una acción en la bitácora del sistema.
     *
     * @param string $accion Acción realizada (ej: 'Ingreso de vehículo', 'Actualización de tarifa')
     * @param string $modulo Módulo donde ocurrió (ej: 'Vehículos', 'Tarifas')
     * @param int|null $id_usuario ID del usuario que realizó la acción
     * @param string|null $detalle Detalles adicionales o en formato JSON/texto
     * @return void
     */
    public static function registrar(string $accion, string $modulo, ?int $id_usuario = null, ?string $detalle = null): void
    {
        $mensaje = sprintf(
            "Bitácora - Módulo: %s | Acción: %s | Usuario ID: %s | Detalle: %s",
            $modulo,
            $accion,
            $id_usuario ?? 'N/A',
            $detalle ?? 'N/A'
        );

        Log::info($mensaje);

        try {
            $ip = request()->ip();
            $id_empresa = null; // En el futuro auth()->user()->id_empresa si está autenticado
            
            \Illuminate\Support\Facades\DB::statement(
                "CALL sp_registrar_bitacora(?, ?, ?, ?, ?, ?)", 
                [$id_usuario, $id_empresa, $accion, $modulo, $detalle, $ip]
            );
        } catch (\Exception $e) {
            // Silencioso si el SP no existe todavía
            Log::warning("sp_registrar_bitacora no ejecutado: " . $e->getMessage());
        }
    }
}

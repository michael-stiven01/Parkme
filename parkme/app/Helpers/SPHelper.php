<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\BitacoraService;

class SPHelper
{
    /**
     * Helper paramétrico para ejecutar Stored Procedures sin escribir SQL crudo manualmente en los servicios.
     * 
     * @param string $sp Nombre del stored procedure (ej: 'sp_listar_tipos_vehiculo')
     * @param array $params Arreglo indexado con los parámetros en el orden exacto requerido
     * @return mixed Devuelve la estructura de datos que arroja el DB::select, o null en caso de error/resolución vacía
     */
    public static function ejecutar(string $sp, array $params = [])
    {
        $placeholders = '';
        if (count($params) > 0) {
            $placeholders = implode(', ', array_fill(0, count($params), '?'));
        }

        $query = "CALL {$sp}({$placeholders})";

        try {
            // Por defecto, ejecutaremos siempre como select a menos que estemos seguros
            // de que el SP no retorna nada. DB::select soporta ambas nativamente para CALL
            return DB::select($query, $params);
            
        } catch (\Exception $e) {
            // Registramos el error a través de Bitácora previniendo logs infinitos en el mismo SP
            if ($sp !== 'sp_registrar_bitacora') {
                try {
                    $id_usuario = auth()->check() ? auth()->id() : null;
                    BitacoraService::registrar(
                        'Ejecución Fallida de SP', 
                        'Base de Datos', 
                        $id_usuario, 
                        "Error en {$sp}: " . $e->getMessage()
                    );
                } catch (\Exception $innerEx) {}
            }
            
            // También se escribe en los Logs de la aplicación directamente
            Log::error("Error SP [{$sp}]: " . $e->getMessage());
            
            return null;
        }
    }
}

<?php

namespace App\Services;

use App\Helpers\SPHelper;
use App\Mail\TicketIngreso;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class ParqueoService
{
    /**
     * Registra el ingreso de un vehículo ya existente al parqueadero.
     *
     * @param array $datos ['id_empresa', 'id_vehiculo', 'id_cupo', 'id_usuario']
     * @return array  Fila del SP o ['error' => código]
     */
    public function registrarIngreso(array $datos): array
    {
        $resultado = SPHelper::ejecutar('sp_ingreso_vehiculo', [
            $datos['id_empresa'],
            $datos['id_vehiculo'],
            $datos['id_cupo'],
            $datos['id_usuario'],
        ]);

        if (empty($resultado)) {
            return ['error' => 'SP_SIN_RESPUESTA'];
        }

        return (array) $resultado[0];
    }

    /**
     * Registra la salida de un vehículo identificado por placa.
     *
     * @param string $placa
     * @param int    $id_empresa
     * @param int    $id_usuario
     * @return array  Fila del SP o ['error' => código]
     */
    public function registrarSalida(string $placa, int $id_empresa, int $id_usuario): array
    {
        $resultado = SPHelper::ejecutar('sp_salida_vehiculo', [
            $id_empresa,
            $placa,
            $id_usuario,
        ]);

        if (empty($resultado)) {
            return ['error' => 'SP_SIN_RESPUESTA'];
        }

        return (array) $resultado[0];
    }

    /**
     * Envía el ticket de ingreso al correo del cliente.
     *
     * @param array $datos_registro Datos devueltos por sp_ingreso_vehiculo
     */
    public function enviarTicketIngreso(array $datos_registro): void
    {
        $correo = $datos_registro['correo_cliente'] ?? null;

        if (! $correo) {
            Log::warning('[ParqueoService] enviarTicketIngreso: correo vacío, no se envía email.', $datos_registro);
            return;
        }

        try {
            Mail::to($correo)->send(new TicketIngreso($datos_registro));

            // Marcar ticket como enviado
            if (! empty($datos_registro['id_registro'])) {
                SPHelper::ejecutar('sp_marcar_ticket_ingreso', [$datos_registro['id_registro']]);
            }
        } catch (Exception $e) {
            Log::error('[ParqueoService] Error enviando ticket de ingreso: ' . $e->getMessage(), [
                'correo'       => $correo,
                'id_registro'  => $datos_registro['id_registro'] ?? null,
            ]);
        }
    }

    /**
     * Envía el ticket de salida/cobro al correo del cliente.
     *
     * @param array $datos_registro Datos devueltos por sp_salida_vehiculo
     */
    public function enviarTicketSalida(array $datos_registro): void
    {
        $correo = $datos_registro['correo_cliente'] ?? null;

        if (! $correo) {
            Log::warning('[ParqueoService] enviarTicketSalida: correo vacío, no se envía email.', $datos_registro);
            return;
        }

        try {
            Mail::to($correo)->send(new \App\Mail\TicketSalida($datos_registro));

            // Marcar ticket de salida como enviado
            if (! empty($datos_registro['id_registro'])) {
                SPHelper::ejecutar('sp_marcar_ticket_salida', [$datos_registro['id_registro']]);
            }
        } catch (Exception $e) {
            Log::error('[ParqueoService] Error enviando ticket de salida: ' . $e->getMessage(), [
                'correo'      => $correo,
                'id_registro' => $datos_registro['id_registro'] ?? null,
            ]);
        }
    }
}

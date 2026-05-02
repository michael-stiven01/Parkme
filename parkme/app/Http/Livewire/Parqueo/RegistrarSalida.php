<?php

namespace App\Http\Livewire\Parqueo;

use Livewire\Component;
use App\Helpers\SPHelper;
use App\Services\ParqueoService;
use App\Services\BitacoraService;

class RegistrarSalida extends Component
{
    // ── Búsqueda ─────────────────────────────────────────────────────────────
    public string  $placa           = '';
    /** null | 'adentro' | 'no_parqueado' | 'no_encontrado' */
    public ?string $estadoVehiculo  = null;
    public ?array  $vehiculoEncontrado = null;
    public ?array  $registroActivo  = null;
    public ?array  $tarifaEstimada  = null;

    // ── Éxito ─────────────────────────────────────────────────────────────────
    public bool  $exito          = false;
    public array $resultadoExito = [];

    // ── Lifecycle ────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.parqueo.registrar-salida')->layout('layouts.app');
    }

    // ── Búsqueda reactiva ────────────────────────────────────────────────────
    public function updatedPlaca(string $valor): void
    {
        $placa = strtoupper(trim($valor));
        $this->placa = $placa;

        // Reset
        $this->estadoVehiculo     = null;
        $this->vehiculoEncontrado = null;
        $this->registroActivo     = null;
        $this->tarifaEstimada     = null;
        $this->resetErrorBag();

        if (strlen($placa) < 3) return;

        $this->buscarVehiculo($placa);
    }

    private function buscarVehiculo(string $placa): void
    {
        $idEmpresa = auth()->user()->id_empresa;

        // 1. ¿Existe el vehículo?
        $resVehiculo = SPHelper::ejecutar('sp_buscar_vehiculo_por_placa', [$idEmpresa, $placa]);

        if (empty($resVehiculo)) {
            $this->estadoVehiculo = 'no_encontrado';
            return;
        }

        $this->vehiculoEncontrado = (array) $resVehiculo[0];

        // 2. ¿Está actualmente en el parqueadero?
        $resCheck = SPHelper::ejecutar('sp_verificar_vehiculo_en_parqueo', [$idEmpresa, $placa]);
        $check    = ! empty($resCheck) ? (array) $resCheck[0] : [];

        if (empty($check['esta_adentro']) || (int)$check['esta_adentro'] !== 1) {
            $this->estadoVehiculo = 'no_parqueado';
            return;
        }

        $this->estadoVehiculo = 'adentro';
        $this->registroActivo = $check;

        // 3. Calcular tarifa estimada con la hora actual
        $this->calcularTarifaEstimada();
    }

    // Llamado también por wire:poll para mantener el estimado actualizado
    public function calcularTarifaEstimada(): void
    {
        if ($this->estadoVehiculo !== 'adentro' || empty($this->vehiculoEncontrado)) return;

        $idEmpresa     = auth()->user()->id_empresa;
        $idTipoVehiculo = (int)($this->vehiculoEncontrado['id_tipo_vehiculo'] ?? 0);
        $fechaIngreso  = $this->registroActivo['fecha_ingreso'] ?? null;

        if (! $fechaIngreso) return;

        $res = SPHelper::ejecutar('sp_calcular_tarifa', [
            $idEmpresa,
            $idTipoVehiculo,
            $fechaIngreso,
            now()->format('Y-m-d H:i:s'),
        ]);

        $this->tarifaEstimada = ! empty($res) ? (array) $res[0] : null;
    }

    // ── Registrar salida ──────────────────────────────────────────────────────
    public function registrarSalida(): void
    {
        if ($this->estadoVehiculo !== 'adentro' || empty($this->vehiculoEncontrado)) {
            session()->flash('error', 'No hay un vehículo activo para procesar la salida.');
            return;
        }

        $idEmpresa = auth()->user()->id_empresa;
        $idUsuario = auth()->user()->id_usuario;

        $servicio = new ParqueoService();
        $datos    = $servicio->registrarSalida($this->placa, $idEmpresa, $idUsuario);

        if (! empty($datos['error'])) {
            $mensajes = [
                'REGISTRO_ACTIVO_NO_ENCONTRADO' => 'No se encontró un registro activo para este vehículo.',
                'SP_SIN_RESPUESTA'              => 'Error de comunicación con la base de datos.',
            ];
            session()->flash('error', $mensajes[$datos['error']] ?? "Error: {$datos['error']}");
            return;
        }

        BitacoraService::registrar(
            'Salida de vehículo registrada',
            'Parqueo',
            $idUsuario,
            "Placa: {$datos['placa']} | Valor: {$datos['valor_cobrado']} | Cupo: {$datos['codigo_cupo']}"
        );

        $servicio->enviarTicketSalida($datos);

        $this->exito          = true;
        $this->resultadoExito = $datos;

        // Limpiar búsqueda
        $this->placa            = '';
        $this->estadoVehiculo   = null;
        $this->vehiculoEncontrado = null;
        $this->registroActivo   = null;
        $this->tarifaEstimada   = null;
    }

    // ── Reset ────────────────────────────────────────────────────────────────
    public function nuevaSalida(): void
    {
        $this->exito          = false;
        $this->resultadoExito = [];
        $this->placa          = '';
        $this->estadoVehiculo = null;
        $this->vehiculoEncontrado = null;
        $this->registroActivo = null;
        $this->tarifaEstimada = null;
        $this->resetErrorBag();
    }
}

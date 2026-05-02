<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Helpers\SPHelper;

class PanelPrincipal extends Component
{
    // ── KPIs ─────────────────────────────────────────────────────────────────
    public int   $vehiculosAdentro    = 0;
    public int   $ingresosHoy         = 0;
    public float $recaudadoHoy        = 0.0;
    public int   $cuposDisponibles    = 0;
    public int   $cuposTotales        = 0;
    public int   $cuposOcupados       = 0;
    public float $ocupacionPorcentaje = 0.0;

    // ── KPIs ayer (para comparar tendencia) ──────────────────────────────────
    public int   $ingresosAyer        = 0;
    public float $recaudadoAyer       = 0.0;

    // ── Datos para gráficas ──────────────────────────────────────────────────
    /** @var array<array{fecha:string,etiqueta:string,total_ingresos:int,total_recaudado:float}> */
    public array $ultimos7Dias        = [];

    /** @var array<array{tipo_vehiculo:string,cantidad:int}> */
    public array $vehiculosPorTipo    = [];

    /** @var array<array{hora:int,etiqueta_hora:string,total_ingresos:int}> */
    public array $ocupacionPorHora    = [];

    // ── Clientes frecuentes ──────────────────────────────────────────────────
    /** @var array<array{nombre_cliente:string,correo_cliente:string,total_visitas:int,ultima_visita:string}> */
    public array $clientesFrecuentes  = [];

    // ── Lifecycle ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->cargarDatos();
    }

    /**
     * Llamado automáticamente por el polling de Livewire cada 60 segundos.
     */
    public function actualizarMetricas(): void
    {
        $this->cargarDatos();
    }

    // ── Carga de datos ───────────────────────────────────────────────────────
    private function cargarDatos(): void
    {
        $idEmpresa = auth()->user()->id_empresa;
        $hoy       = now()->toDateString();
        $ayer      = now()->subDay()->toDateString();

        // KPIs de hoy
        $kpisHoy = SPHelper::ejecutar('sp_kpis_dashboard', [$idEmpresa, $hoy]);
        if (!empty($kpisHoy)) {
            $k = (array) $kpisHoy[0];
            $this->vehiculosAdentro    = (int)   ($k['vehiculos_adentro']   ?? 0);
            $this->ingresosHoy         = (int)   ($k['ingresos_hoy']        ?? 0);
            $this->recaudadoHoy        = (float) ($k['recaudado_hoy']       ?? 0);
            $this->cuposDisponibles    = (int)   ($k['cupos_disponibles']   ?? 0);
            $this->cuposTotales        = (int)   ($k['cupos_totales']       ?? 0);
            $this->cuposOcupados       = (int)   ($k['cupos_ocupados']      ?? 0);
            $this->ocupacionPorcentaje = (float) ($k['ocupacion_porcentaje'] ?? 0);
        }

        // KPIs de ayer (para tendencias)
        $kpisAyer = SPHelper::ejecutar('sp_kpis_dashboard', [$idEmpresa, $ayer]);
        if (!empty($kpisAyer)) {
            $ka = (array) $kpisAyer[0];
            $this->ingresosAyer  = (int)   ($ka['ingresos_hoy']  ?? 0);
            $this->recaudadoAyer = (float) ($ka['recaudado_hoy'] ?? 0);
        }

        // Gráfica de barras — últimos 7 días
        $dias = SPHelper::ejecutar('sp_ingresos_ultimos_7_dias', [$idEmpresa]);
        $this->ultimos7Dias = $dias
            ? array_map(fn($r) => (array) $r, $dias)
            : [];

        // Gráfica donut — tipos de vehículo hoy
        $tipos = SPHelper::ejecutar('sp_vehiculos_por_tipo_hoy', [$idEmpresa, $hoy]);
        $this->vehiculosPorTipo = $tipos
            ? array_map(fn($r) => (array) $r, $tipos)
            : [];

        // Gráfica de barras pequeña — ocupación por hora hoy
        $horas = SPHelper::ejecutar('sp_ocupacion_por_hora', [$idEmpresa, $hoy]);
        $this->ocupacionPorHora = $horas
            ? array_map(fn($r) => (array) $r, $horas)
            : [];

        // Tabla — top 5 clientes frecuentes
        $clientes = SPHelper::ejecutar('sp_clientes_frecuentes', [$idEmpresa, 5]);
        $this->clientesFrecuentes = $clientes
            ? array_map(fn($r) => (array) $r, $clientes)
            : [];

        // Notificar al JS para que reinicialice las gráficas con los datos frescos
        $this->dispatch('graficas-actualizadas', [
            'dias7' => $this->ultimos7Dias,
            'tipos' => $this->vehiculosPorTipo,
            'horas' => $this->ocupacionPorHora,
        ]);
    }

    // ── Helpers de vista ─────────────────────────────────────────────────────

    /**
     * Devuelve 'up' | 'down' | 'neutral' comparando dos valores.
     */
    public function tendencia(float|int $hoy, float|int $ayer): string
    {
        if ($hoy > $ayer) return 'up';
        if ($hoy < $ayer) return 'down';
        return 'neutral';
    }

    /**
     * Formatea un valor numérico como moneda COP con separador de miles.
     */
    public function formatearCOP(float $valor): string
    {
        return '$ ' . number_format($valor, 0, ',', '.');
    }

    // ── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.dashboard.panel-principal')->layout('layouts.app');
    }
}

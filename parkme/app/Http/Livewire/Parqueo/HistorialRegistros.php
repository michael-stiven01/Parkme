<?php

namespace App\Http\Livewire\Parqueo;

use Livewire\Component;
use App\Helpers\SPHelper;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistorialRegistros extends Component
{
    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $fechaInicio = '';
    public string $fechaFin    = '';
    public string $busqueda    = '';

    // ── Datos ─────────────────────────────────────────────────────────────────
    public array $registros    = [];
    public int   $pagina       = 1;
    public int   $porPagina    = 15;

    // ── Totales ───────────────────────────────────────────────────────────────
    public int   $totalRegistros = 0;
    public float $totalCobrado   = 0.0;

    // ─────────────────────────────────────────────────────────────────────────
    public function mount(): void
    {
        // Rango por defecto: hoy
        $this->fechaInicio = now()->format('Y-m-d');
        $this->fechaFin    = now()->format('Y-m-d');
        $this->cargarRegistros();
    }

    public function render()
    {
        $paginados = collect($this->registros)
            ->slice(($this->pagina - 1) * $this->porPagina, $this->porPagina)
            ->values()
            ->all();

        $totalPaginas = (int) ceil(count($this->registros) / $this->porPagina);

        return view('livewire.parqueo.historial-registros', [
            'paginados'    => $paginados,
            'totalPaginas' => max(1, $totalPaginas),
        ])->layout('layouts.app');
    }

    // ── Carga ─────────────────────────────────────────────────────────────────
    public function cargarRegistros(): void
    {
        $idEmpresa = auth()->user()->id_empresa;

        $resultado = SPHelper::ejecutar('sp_historial_registros', [
            $idEmpresa,
            $this->fechaInicio ?: null,
            $this->fechaFin    ?: null,
            $this->busqueda    ?: null,
        ]);

        $this->registros = $resultado ?? [];

        // Totales sobre el conjunto completo (no solo la página)
        $col = collect($this->registros);
        $this->totalRegistros = $col->count();
        $this->totalCobrado   = (float) $col->sum(fn ($r) => $r->VALOR_COBRADO ?? 0);

        // Volver a página 1 al recargar
        $this->pagina = 1;
    }

    // ── Reactive watchers ─────────────────────────────────────────────────────
    public function updatedFechaInicio(): void { $this->cargarRegistros(); }
    public function updatedFechaFin(): void    { $this->cargarRegistros(); }
    public function updatedBusqueda(): void    { $this->cargarRegistros(); }

    // ── Paginación ────────────────────────────────────────────────────────────
    public function irPagina(int $pagina): void
    {
        $totalPaginas = (int) ceil(count($this->registros) / $this->porPagina);
        $this->pagina = max(1, min($pagina, $totalPaginas));
    }

    public function paginaAnterior(): void { $this->irPagina($this->pagina - 1); }
    public function paginaSiguiente(): void { $this->irPagina($this->pagina + 1); }

    // ── Exportar CSV ──────────────────────────────────────────────────────────
    public function exportarCsv(): StreamedResponse
    {
        $registros = $this->registros; // snapshot del conjunto filtrado actual

        $nombre = 'historial_parqueo_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($registros) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para compatibilidad con Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabecera
            fputcsv($handle, [
                'N° Registro',
                'Placa',
                'Cliente',
                'Tipo Vehículo',
                'Cupo',
                'Fecha Ingreso',
                'Fecha Salida',
                'Duración (min)',
                'Duración (HH:MM)',
                'Tarifa Aplicada',
                'Valor Cobrado (COP)',
                'Usuario Ingreso',
                'Usuario Salida',
            ], separator: ',');

            foreach ($registros as $r) {
                $min = (int)($r->TIEMPO_MINUTOS ?? 0);
                $h   = intdiv($min, 60);
                $m   = $min % 60;
                $duracionFormato = sprintf('%02d:%02d', $h, $m);

                fputcsv($handle, [
                    $r->id_registro       ?? '',
                    $r->PLACA             ?? '',
                    $r->nombre_cliente    ?? '',
                    $r->tipo_vehiculo     ?? '',
                    $r->codigo_cupo       ?? '',
                    $r->FECHA_INGRESO     ?? '',
                    $r->FECHA_SALIDA      ?? '',
                    $r->TIEMPO_MINUTOS    ?? 0,
                    $duracionFormato,
                    $r->nombre_tarifa     ?? 'Sin Tarifa',
                    $r->VALOR_COBRADO     ?? 0,
                    $r->usuario_ingreso   ?? '',
                    $r->usuario_salida    ?? '',
                ], separator: ',');
            }

            fclose($handle);
        }, $nombre, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
        ]);
    }

    // ── Limpiar filtros ───────────────────────────────────────────────────────
    public function limpiarFiltros(): void
    {
        $this->fechaInicio = now()->format('Y-m-d');
        $this->fechaFin    = now()->format('Y-m-d');
        $this->busqueda    = '';
        $this->cargarRegistros();
    }
}

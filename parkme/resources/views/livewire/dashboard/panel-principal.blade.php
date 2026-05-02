<div wire:poll.60000ms="actualizarMetricas">

    {{-- ═══════════════════════════════════════════════════════════════════
         ENCABEZADO
    ════════════════════════════════════════════════════════════════════════ --}}
    <x-molecules.encabezado-modulo
        titulo="Dashboard"
        descripcion="Panel de control en tiempo real — actualización automática cada 60 segundos"
        icono="chart-bar"
    />

    {{-- ═══════════════════════════════════════════════════════════════════
         SECCIÓN A — KPI CARDS
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4 mb-6">

        {{-- KPI 1 — Vehículos adentro --}}
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vehículos adentro</p>
                    <p class="mt-1 text-4xl font-extrabold text-gray-900 dark:text-white tabular-nums">
                        {{ $vehiculosAdentro }}
                    </p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-indigo-500/10 flex items-center justify-center" style="color:#6366f1">
                    <x-atoms.icono nombre="truck" tamaño="lg" />
                </div>
            </div>
            {{-- Barra de ocupación --}}
            <div>
                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                    <span>{{ $cuposOcupados }} ocupados</span>
                    <span>{{ $cuposTotales }} totales</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-indigo-500 h-2 rounded-full transition-all duration-700"
                         style="width: {{ $cuposTotales > 0 ? min(100, round(($cuposOcupados / $cuposTotales) * 100)) : 0 }}%">
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI 2 — Ingresos hoy (cantidad) --}}
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ingresos hoy</p>
                    <p class="mt-1 text-4xl font-extrabold text-gray-900 dark:text-white tabular-nums">
                        {{ $ingresosHoy }}
                    </p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-emerald-500/10 flex items-center justify-center" style="color:#10b981">
                    <x-atoms.icono nombre="arrow-right-circle" tamaño="lg" />
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                @php $tendIngr = $this->tendencia($ingresosHoy, $ingresosAyer); @endphp
                @if($tendIngr === 'up')
                    <span style="color:#10b981"><x-atoms.icono nombre="arrow-trending-up" tamaño="sm" /></span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Más que ayer ({{ $ingresosAyer }})</span>
                @elseif($tendIngr === 'down')
                    <span style="color:#f43f5e"><x-atoms.icono nombre="arrow-trending-down" tamaño="sm" /></span>
                    <span class="text-rose-600 dark:text-rose-400 font-medium">Menos que ayer ({{ $ingresosAyer }})</span>
                @else
                    <span style="color:#9ca3af"><x-atoms.icono nombre="minus" tamaño="sm" /></span>
                    <span class="text-gray-500 dark:text-gray-400">Igual que ayer</span>
                @endif
            </div>
        </div>

        {{-- KPI 3 — Recaudado hoy (COP) --}}
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Recaudado hoy</p>
                    <p class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums leading-tight">
                        {{ $this->formatearCOP($recaudadoHoy) }}
                    </p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-amber-500/10 flex items-center justify-center" style="color:#f59e0b">
                    <x-atoms.icono nombre="banknotes" tamaño="lg" />
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                @php $tendRec = $this->tendencia($recaudadoHoy, $recaudadoAyer); @endphp
                @if($tendRec === 'up')
                    <span style="color:#10b981"><x-atoms.icono nombre="arrow-trending-up" tamaño="sm" /></span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Ayer: {{ $this->formatearCOP($recaudadoAyer) }}</span>
                @elseif($tendRec === 'down')
                    <span style="color:#f43f5e"><x-atoms.icono nombre="arrow-trending-down" tamaño="sm" /></span>
                    <span class="text-rose-600 dark:text-rose-400 font-medium">Ayer: {{ $this->formatearCOP($recaudadoAyer) }}</span>
                @else
                    <span style="color:#9ca3af"><x-atoms.icono nombre="minus" tamaño="sm" /></span>
                    <span class="text-gray-500 dark:text-gray-400">Igual que ayer</span>
                @endif
            </div>
        </div>

        {{-- KPI 4 — Cupos disponibles con indicador de anillo --}}
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cupos disponibles</p>
                    <p class="mt-1 text-4xl font-extrabold text-gray-900 dark:text-white tabular-nums">
                        {{ $cuposDisponibles }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">de {{ $cuposTotales }} activos</p>
                </div>
                {{-- Anillo SVG de porcentaje --}}
                @php
                    $pct   = $cuposTotales > 0 ? min(100, round(($cuposOcupados / $cuposTotales) * 100)) : 0;
                    $radio = 18;
                    $circ  = 2 * M_PI * $radio;
                    $dash  = round(($pct / 100) * $circ, 2);
                    $gap   = round($circ - $dash, 2);
                    $color = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#10b981');
                @endphp
                <div class="relative h-14 w-14">
                    <svg viewBox="0 0 44 44" class="h-14 w-14 -rotate-90">
                        <circle cx="22" cy="22" r="{{ $radio }}" fill="none" stroke="currentColor"
                                stroke-width="4" class="text-gray-100 dark:text-gray-700"/>
                        <circle cx="22" cy="22" r="{{ $radio }}" fill="none"
                                stroke="{{ $color }}" stroke-width="4"
                                stroke-dasharray="{{ $dash }} {{ $gap }}"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-gray-700 dark:text-gray-200">{{ $pct }}%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /KPI Cards --}}

    {{-- ═══════════════════════════════════════════════════════════════════
         SECCIÓN B — GRÁFICAS PRINCIPALES
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 mb-6">

        {{-- Gráfica de barras — últimos 7 días (span 2 columnas) --}}
        <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Ingresos de los últimos 7 días</h2>
            <div class="relative h-56" wire:ignore>
                <canvas id="grafica-7-dias"></canvas>
            </div>
        </div>

        {{-- Gráfica donut — tipos de vehículo hoy --}}
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Tipos de vehículos hoy</h2>
            <div class="relative h-48 flex items-center justify-center" wire:ignore>
                <canvas id="grafica-tipos"></canvas>
            </div>
            <div class="mt-3 space-y-1.5" id="leyenda-tipos" wire:ignore></div>
        </div>

    </div>{{-- /Charts --}}

    {{-- ═══════════════════════════════════════════════════════════════════
         SECCIÓN C — MÉTRICAS SECUNDARIAS
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 mb-6">

        {{-- Ocupación por hora --}}
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Ocupación por hora — hoy</h2>
            <div class="relative h-44" wire:ignore>
                <canvas id="grafica-horas"></canvas>
            </div>
        </div>

        {{-- Clientes más frecuentes --}}
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Clientes más frecuentes</h2>
            @if(count($clientesFrecuentes) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="text-left py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                            <th class="text-right py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visitas</th>
                            <th class="text-right py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Última visita</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($clientesFrecuentes as $i => $cliente)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-2.5 pr-2">
                                <span class="inline-flex items-center justify-center h-5 w-5 rounded-full text-[10px] font-bold
                                    {{ $i === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                                       ($i === 1 ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' :
                                       ($i === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' :
                                       'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-500')) }}">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td class="py-2.5">
                                <div class="font-medium text-gray-900 dark:text-white truncate max-w-[150px]">
                                    {{ $cliente['nombre_cliente'] }}
                                </div>
                                <div class="text-xs text-gray-400 truncate max-w-[150px]">
                                    {{ $cliente['correo_cliente'] }}
                                </div>
                            </td>
                            <td class="py-2.5 text-right">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    {{ $cliente['total_visitas'] }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($cliente['ultima_visita'])->format('d/m/Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="h-40 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 gap-2">
                <x-atoms.icono nombre="user-group" tamaño="xl" />
                <p class="text-xs">Sin datos de clientes aún</p>
            </div>
            @endif
        </div>

    </div>{{-- /Secondary metrics --}}

    {{-- ═══════════════════════════════════════════════════════════════════
         SECCIÓN D — ACCIONES RÁPIDAS
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-2">
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Acciones rápidas</h2>
        <div class="flex flex-wrap gap-3">

            <a href="{{ route('parqueo.ingreso') }}"
               id="btn-accion-ingreso"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                      bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
                      text-white shadow-sm hover:shadow-indigo-200 dark:hover:shadow-indigo-900/40
                      transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <x-atoms.icono nombre="arrow-right-circle" tamaño="sm" />
                Registrar ingreso
            </a>

            <a href="{{ route('parqueo.salida') }}"
               id="btn-accion-salida"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                      bg-rose-600 hover:bg-rose-700 active:bg-rose-800
                      text-white shadow-sm hover:shadow-rose-200 dark:hover:shadow-rose-900/40
                      transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <x-atoms.icono nombre="arrow-left-circle" tamaño="sm" />
                Registrar salida
            </a>

            <a href="{{ route('cupos') }}"
               id="btn-accion-cupos"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                      bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600
                      text-gray-700 dark:text-gray-200
                      hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-300
                      shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <x-atoms.icono nombre="squares-2x2" tamaño="sm" />
                Ver cupos en mapa
            </a>

        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const isDark = () => document.documentElement.classList.contains('dark') || window.matchMedia('(prefers-color-scheme: dark)').matches;
    const palette = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6'];
    const gridColor  = () => isDark() ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.08)';
    const tickColor  = () => isDark() ? '#ffffff' : '#334155';
    const labelColor = () => isDark() ? '#e2e8f0' : '#475569';

    // Almacén de instancias para destruirlas antes de reiniciar
    const instancias = {};

    function destruir(id) {
        if (instancias[id]) { instancias[id].destroy(); delete instancias[id]; }
    }

    function iniciarGrafica7Dias(dias7) {
        destruir('dias7');
        const el = document.getElementById('grafica-7-dias');
        if (!el || !dias7.length) return;
        instancias['dias7'] = new Chart(el, {
            type: 'bar',
            data: {
                labels: dias7.map(d => d.etiqueta),
                datasets: [{
                    label: 'Vehículos ingresados',
                    data: dias7.map(d => d.total_ingresos),
                    backgroundColor: 'rgba(99,102,241,0.80)',
                    borderColor: '#6366f1',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, labels: { color: labelColor(), font: { size: 11, family: 'Poppins' } } },
                    tooltip: { callbacks: { label: c => ` ${c.parsed.y} vehículos` } },
                },
                scales: {
                    x: { grid: { color: gridColor() }, ticks: { color: tickColor(), font: { size: 11, family: 'Poppins' } } },
                    y: { beginAtZero: true, grid: { color: gridColor() }, ticks: { color: tickColor(), font: { size: 11, family: 'Poppins' }, stepSize: 1, precision: 0 } },
                },
            },
        });
    }

    function iniciarGraficaTipos(tipos) {
        destruir('tipos');
        const el = document.getElementById('grafica-tipos');
        if (!el || !tipos.length) return;
        instancias['tipos'] = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: tipos.map(t => t.tipo_vehiculo),
                datasets: [{ data: tipos.map(t => t.cantidad), backgroundColor: palette.slice(0, tipos.length), borderWidth: 2, borderColor: isDark() ? '#1f2937' : '#ffffff', hoverOffset: 6 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed} vehículos` } },
                },
            },
        });
        const leyenda = document.getElementById('leyenda-tipos');
        if (leyenda) {
            leyenda.innerHTML = tipos.map((t, i) =>
                `<div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block h-2.5 w-2.5 rounded-full flex-shrink-0" style="background:${palette[i]}"></span>
                        <span style="color:${isDark()?'#cbd5e1':'#4b5563'}">${t.tipo_vehiculo}</span>
                    </div>
                    <span style="font-weight:600;color:${isDark()?'#e2e8f0':'#1e293b'}">${t.cantidad}</span>
                </div>`
            ).join('');
        }
    }

    function iniciarGraficaHoras(horas) {
        destruir('horas');
        const el = document.getElementById('grafica-horas');
        if (!el || !horas.length) return;
        instancias['horas'] = new Chart(el, {
            type: 'bar',
            data: {
                labels: horas.map(h => h.etiqueta_hora + ':00'),
                datasets: [{ label: 'Ingresos', data: horas.map(h => h.total_ingresos), backgroundColor: 'rgba(16,185,129,0.75)', borderColor: '#10b981', borderWidth: 1.5, borderRadius: 4, borderSkipped: false }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ` ${c.parsed.y} ingreso(s)` } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: tickColor(), font: { size: 10, family: 'Poppins' } } },
                    y: { beginAtZero: true, grid: { color: gridColor() }, ticks: { color: tickColor(), font: { size: 10, family: 'Poppins' }, stepSize: 1, precision: 0 } },
                },
            },
        });
    }

    // Inicialización en la primera carga
    function iniciarTodo(datos) {
        iniciarGrafica7Dias(datos.dias7);
        iniciarGraficaTipos(datos.tipos);
        iniciarGraficaHoras(datos.horas);
    }

    // Primera carga — diferida un frame para que el canvas tenga dimensiones
    requestAnimationFrame(() => {
        iniciarTodo({
            dias7: @json($ultimos7Dias),
            tipos: @json($vehiculosPorTipo),
            horas: @json($ocupacionPorHora),
        });
    });

    // Reinicializar tras cada ciclo de polling de Livewire
    window.addEventListener('graficas-actualizadas', e => iniciarTodo(e.detail));
})();
</script>
@endpush

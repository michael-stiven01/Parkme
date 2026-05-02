<div>
    <x-molecules.encabezado-modulo titulo="Historial de Registros" descripcion="Registros finalizados de ingreso y salida" icono="clock">
        {{-- Exportar CSV --}}
        <button wire:click="exportarCsv" id="btn-exportar-csv"
            class="inline-flex items-center gap-x-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-slate-300 shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
            <x-atoms.icono nombre="arrow-down-tray" tamaño="sm" class="text-gray-500 dark:text-slate-400" />
            Exportar CSV
        </button>
    </x-molecules.encabezado-modulo>

    {{-- ── FILTROS ─────────────────────────────────────────────────────────── --}}
    <div class="mt-6 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800/50 p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            {{-- Fecha inicio --}}
            <div>
                <label for="filtro-fecha-inicio" class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1 uppercase tracking-wide">
                    Desde
                </label>
                <input type="date" id="filtro-fecha-inicio"
                    wire:model.live="fechaInicio"
                    class="block w-full rounded-lg border-0 py-2 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-900 dark:text-white dark:ring-slate-700">
            </div>

            {{-- Fecha fin --}}
            <div>
                <label for="filtro-fecha-fin" class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1 uppercase tracking-wide">
                    Hasta
                </label>
                <input type="date" id="filtro-fecha-fin"
                    wire:model.live="fechaFin"
                    class="block w-full rounded-lg border-0 py-2 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-900 dark:text-white dark:ring-slate-700">
            </div>

            {{-- Búsqueda --}}
            <div>
                <label for="filtro-busqueda" class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1 uppercase tracking-wide">
                    Placa o Cliente
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-atoms.icono nombre="magnifying-glass" tamaño="sm" class="text-gray-400" />
                    </div>
                    <input type="text" id="filtro-busqueda"
                        wire:model.live.debounce.400ms="busqueda"
                        placeholder="Buscar..."
                        class="block w-full rounded-lg border-0 py-2 pl-9 pr-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-900 dark:text-white dark:ring-slate-700 dark:placeholder:text-slate-500">
                </div>
            </div>
        </div>

        {{-- Limpiar filtros --}}
        <div class="mt-3 flex justify-end">
            <button wire:click="limpiarFiltros" id="btn-limpiar-filtros"
                class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium flex items-center gap-1">
                <x-atoms.icono nombre="arrow-path" tamaño="sm" />
                Restablecer a hoy
            </button>
        </div>
    </div>

    {{-- ── TABLA ───────────────────────────────────────────────────────────── --}}
    <div class="mt-6">

        {{-- Contador de resultados --}}
        <div class="mb-3 flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-slate-400">
                <span class="font-semibold text-gray-800 dark:text-white">{{ $totalRegistros }}</span>
                registro{{ $totalRegistros !== 1 ? 's' : '' }} encontrado{{ $totalRegistros !== 1 ? 's' : '' }}
            </p>
            <div wire:loading wire:target="cargarRegistros,updatedFechaInicio,updatedFechaFin,updatedBusqueda">
                <x-atoms.icono nombre="arrow-path" tamaño="sm" class="animate-spin text-indigo-500" />
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl shadow ring-1 ring-black ring-opacity-5">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700/50">
                <thead class="bg-gray-50 dark:bg-slate-800/80">
                    <tr>
                        <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Placa</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Cliente</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide hidden md:table-cell">Tipo</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide hidden lg:table-cell">Cupo</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Ingreso</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide hidden sm:table-cell">Salida</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide hidden sm:table-cell">Duración</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide hidden xl:table-cell">Tarifa</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Cobrado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700/50 bg-white dark:bg-slate-900/50">

                    @forelse($paginados as $reg)
                    @php
                        $min = (int)($reg->TIEMPO_MINUTOS ?? 0);
                        $h   = intdiv($min, 60);
                        $m   = $min % 60;
                        $dur = sprintf('%02d:%02d', $h, $m);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/40 transition-colors">
                        {{-- Placa --}}
                        <td class="whitespace-nowrap py-3.5 pl-4 pr-3">
                            <span class="text-sm font-bold tracking-widest text-indigo-600 dark:text-indigo-400">
                                {{ $reg->PLACA }}
                            </span>
                        </td>
                        {{-- Cliente --}}
                        <td class="px-3 py-3.5">
                            <p class="text-sm font-medium text-gray-900 dark:text-white leading-tight">{{ $reg->nombre_cliente }}</p>
                            <p class="text-xs text-gray-400 dark:text-slate-500 truncate max-w-[140px]">{{ $reg->correo_cliente }}</p>
                        </td>
                        {{-- Tipo --}}
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-500 dark:text-slate-400 hidden md:table-cell">
                            {{ $reg->tipo_vehiculo ?? '—' }}
                        </td>
                        {{-- Cupo --}}
                        <td class="whitespace-nowrap px-3 py-3.5 hidden lg:table-cell">
                            @if($reg->codigo_cupo)
                                <span class="inline-flex items-center rounded-md bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-600/20">
                                    {{ $reg->codigo_cupo }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        {{-- Ingreso --}}
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600 dark:text-slate-300">
                            @if($reg->FECHA_INGRESO)
                                <p class="font-medium">{{ \Carbon\Carbon::parse($reg->FECHA_INGRESO)->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($reg->FECHA_INGRESO)->format('H:i') }}</p>
                            @else
                                —
                            @endif
                        </td>
                        {{-- Salida --}}
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600 dark:text-slate-300 hidden sm:table-cell">
                            @if($reg->FECHA_SALIDA)
                                <p class="font-medium">{{ \Carbon\Carbon::parse($reg->FECHA_SALIDA)->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($reg->FECHA_SALIDA)->format('H:i') }}</p>
                            @else
                                —
                            @endif
                        </td>
                        {{-- Duración --}}
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm font-mono text-gray-700 dark:text-slate-300 hidden sm:table-cell">
                            {{ $dur }}
                        </td>
                        {{-- Tarifa --}}
                        <td class="whitespace-nowrap px-3 py-3.5 text-xs text-gray-500 dark:text-slate-400 hidden xl:table-cell">
                            {{ $reg->nombre_tarifa ?? 'Sin Tarifa' }}
                        </td>
                        {{-- Cobrado --}}
                        <td class="whitespace-nowrap px-3 py-3.5 text-right">
                            <span class="text-sm font-bold {{ ($reg->VALOR_COBRADO ?? 0) > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
                                ${{ number_format((float)($reg->VALOR_COBRADO ?? 0), 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-16 text-center">
                            <x-atoms.icono nombre="clock" tamaño="lg" class="mx-auto mb-3 text-gray-300 dark:text-slate-600" />
                            <p class="text-sm font-medium text-gray-500 dark:text-slate-400">No hay registros en el período seleccionado</p>
                            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Ajusta el rango de fechas o la búsqueda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                {{-- Fila de totales --}}
                @if($totalRegistros > 0)
                <tfoot class="bg-gray-50 dark:bg-slate-800/80 border-t-2 border-gray-200 dark:border-slate-700">
                    <tr>
                        <td colspan="8" class="py-3 pl-4 pr-3 text-sm font-bold text-gray-700 dark:text-slate-300 text-right">
                            Total ({{ $totalRegistros }} registros):
                        </td>
                        <td class="py-3 px-3 text-right">
                            <span class="text-base font-black text-green-600 dark:text-green-400">
                                ${{ number_format($totalCobrado, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- ── PAGINACIÓN ──────────────────────────────────────────────────── --}}
        @if($totalPaginas > 1)
        <div class="mt-4 flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-slate-400">
                Página <span class="font-semibold text-gray-800 dark:text-white">{{ $pagina }}</span>
                de <span class="font-semibold text-gray-800 dark:text-white">{{ $totalPaginas }}</span>
            </p>
            <div class="flex items-center gap-1">
                <button wire:click="paginaAnterior" id="btn-pag-anterior"
                    @disabled($pagina <= 1)
                    class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium
                        {{ $pagina <= 1
                            ? 'text-gray-300 dark:text-slate-600 cursor-not-allowed'
                            : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors' }}">
                    <x-atoms.icono nombre="chevron-left" tamaño="sm" />
                    Anterior
                </button>

                @php
                    $inicio = max(1, $pagina - 2);
                    $fin    = min($totalPaginas, $pagina + 2);
                @endphp
                @for($p = $inicio; $p <= $fin; $p++)
                <button wire:click="irPagina({{ $p }})"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm font-semibold transition-colors
                        {{ $p === $pagina
                            ? 'bg-indigo-600 text-white'
                            : 'text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700' }}">
                    {{ $p }}
                </button>
                @endfor

                <button wire:click="paginaSiguiente" id="btn-pag-siguiente"
                    @disabled($pagina >= $totalPaginas)
                    class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium
                        {{ $pagina >= $totalPaginas
                            ? 'text-gray-300 dark:text-slate-600 cursor-not-allowed'
                            : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors' }}">
                    Siguiente
                    <x-atoms.icono nombre="chevron-right" tamaño="sm" />
                </button>
            </div>
        </div>
        @endif

    </div>
</div>

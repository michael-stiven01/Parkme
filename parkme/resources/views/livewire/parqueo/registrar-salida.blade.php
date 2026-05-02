{{-- Polling each 60s to keep estimated fee fresh while vehicle card is shown --}}
<div wire:poll.60000ms="calcularTarifaEstimada">

    <x-molecules.encabezado-modulo titulo="Registrar Salida" descripcion="Procesa la salida y el cobro del vehículo" icono="arrow-left-circle" />

    {{-- Flash error --}}
    @if(session()->has('error'))
    <div class="mt-4 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 flex items-center gap-3">
        <x-atoms.icono nombre="x-circle" tamaño="sm" class="text-red-500 shrink-0" />
        <p class="text-sm font-medium text-red-700 dark:text-red-400">{{ session('error') }}</p>
    </div>
    @endif

    {{-- ══ PANTALLA DE ÉXITO ══════════════════════════════════════════════════ --}}
    @if($exito)
    <div class="mt-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border border-indigo-200 dark:border-indigo-800/50 p-8 shadow-sm">

        <div class="text-center mb-6">
            <div class="mx-auto mb-3 h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                <x-atoms.icono nombre="check-badge" tamaño="lg" class="text-indigo-600 dark:text-indigo-400" />
            </div>
            <h2 class="text-xl font-bold text-indigo-800 dark:text-indigo-300 mb-1">¡Salida Registrada!</h2>
            <p class="text-sm text-indigo-500 dark:text-indigo-400">El comprobante ha sido enviado al correo del cliente.</p>
        </div>

        {{-- Recibo final --}}
        <div class="mx-auto max-w-md rounded-xl bg-white dark:bg-slate-800 border border-indigo-200 dark:border-indigo-800/40 shadow divide-y divide-gray-100 dark:divide-slate-700">

            {{-- Cabecera recibo --}}
            <div class="px-5 py-4 flex items-center justify-between bg-gray-50 dark:bg-slate-700/50 rounded-t-xl">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">N.° Registro</p>
                    <p class="text-sm font-bold text-gray-800 dark:text-white">#{{ str_pad($resultadoExito['id_registro'] ?? 0, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
                <span class="text-2xl font-black tracking-widest text-indigo-600 dark:text-indigo-400">
                    {{ $resultadoExito['placa'] ?? '—' }}
                </span>
            </div>

            <div class="px-5 py-3 flex justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cliente</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ $resultadoExito['nombre_cliente'] ?? '—' }}</span>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cupo</span>
                <span class="text-sm font-bold text-gray-800 dark:text-white">{{ $resultadoExito['codigo_cupo'] ?? '—' }}</span>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ingreso</span>
                <span class="text-sm text-gray-600 dark:text-slate-300">
                    @if(!empty($resultadoExito['fecha_ingreso']))
                        {{ \Carbon\Carbon::parse($resultadoExito['fecha_ingreso'])->format('d/m/Y H:i') }}
                    @else — @endif
                </span>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Salida</span>
                <span class="text-sm text-gray-600 dark:text-slate-300">
                    @if(!empty($resultadoExito['fecha_salida']))
                        {{ \Carbon\Carbon::parse($resultadoExito['fecha_salida'])->format('d/m/Y H:i') }}
                    @else — @endif
                </span>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Duración</span>
                @php
                    $min = (int)($resultadoExito['tiempo_minutos'] ?? 0);
                    $h   = intdiv($min, 60);
                    $m   = $min % 60;
                @endphp
                <span class="text-sm font-semibold text-gray-800 dark:text-white">
                    {{ $h > 0 ? "{$h}h {$m}min" : "{$m} min" }}
                </span>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Tarifa</span>
                <span class="text-sm text-gray-600 dark:text-slate-400">{{ $resultadoExito['nombre_tarifa'] ?? 'Sin tarifa' }}</span>
            </div>

            {{-- Monto destacado --}}
            <div class="px-5 py-4 flex items-center justify-between bg-green-50 dark:bg-green-900/20 rounded-b-xl">
                <span class="text-sm font-bold text-green-700 dark:text-green-400 uppercase tracking-wide">Total cobrado</span>
                <span class="text-2xl font-black text-green-600 dark:text-green-400">
                    ${{ number_format((float)($resultadoExito['valor_cobrado'] ?? 0), 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button wire:click="nuevaSalida" id="btn-nueva-salida"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition-all">
                <x-atoms.icono nombre="plus" tamaño="sm" />
                Nueva Salida
            </button>
        </div>
    </div>

    @else

    {{-- ══ FORMULARIO BÚSQUEDA ════════════════════════════════════════════════ --}}
    <div class="mt-8 max-w-xl mx-auto">

        {{-- Campo placa --}}
        <div>
            <label for="input-placa-salida" class="block text-sm font-medium text-gray-900 dark:text-gray-300 mb-1">
                Placa del vehículo <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    type="text"
                    id="input-placa-salida"
                    wire:model.live.debounce.400ms="placa"
                    maxlength="10"
                    style="text-transform:uppercase;letter-spacing:3px;"
                    placeholder="Ej: ABC123"
                    autocomplete="off"
                    class="block w-full rounded-xl border-0 py-3.5 pl-4 pr-12 text-xl font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-300 placeholder:font-normal placeholder:tracking-normal focus:ring-2 focus:ring-indigo-600 dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                >
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    @if($estadoVehiculo === 'adentro')
                        <x-atoms.icono nombre="check-circle" tamaño="sm" class="text-green-500" />
                    @elseif($estadoVehiculo === 'no_parqueado' || $estadoVehiculo === 'no_encontrado')
                        <x-atoms.icono nombre="x-circle" tamaño="sm" class="text-red-400" />
                    @else
                        <x-atoms.icono nombre="magnifying-glass" tamaño="sm" class="text-gray-400" />
                    @endif
                </div>
            </div>
            <p class="mt-1 text-xs text-gray-400">Escribe la placa para buscar automáticamente.</p>
        </div>

        {{-- Estado: no encontrado --}}
        @if($estadoVehiculo === 'no_encontrado')
        <div class="mt-4 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-4 flex items-center gap-3">
            <x-atoms.icono nombre="question-mark-circle" tamaño="sm" class="text-gray-400 shrink-0" />
            <p class="text-sm text-gray-600 dark:text-slate-400">No se encontró ningún vehículo con esta placa.</p>
        </div>
        @endif

        {{-- Estado: encontrado pero no parqueado --}}
        @if($estadoVehiculo === 'no_parqueado')
        <div class="mt-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 flex items-center gap-3">
            <x-atoms.icono nombre="information-circle" tamaño="sm" class="text-amber-500 shrink-0" />
            <p class="text-sm text-amber-700 dark:text-amber-400">
                Este vehículo <strong>({{ $placa }})</strong> no se encuentra en el parqueadero actualmente.
            </p>
        </div>
        @endif

        {{-- Estado: adentro → tarjeta de resumen --}}
        @if($estadoVehiculo === 'adentro' && $registroActivo && $vehiculoEncontrado)
        @php
            $fechaIngreso = $registroActivo['fecha_ingreso'] ?? null;
        @endphp
        <div class="mt-4 rounded-xl border border-indigo-200 dark:border-indigo-800/50 bg-white dark:bg-slate-800 shadow-sm overflow-hidden"
             x-data="{
                 fechaIngreso: '{{ $fechaIngreso }}',
                 tiempoTexto: '',
                 init() {
                     this.actualizar();
                     setInterval(() => this.actualizar(), 60000);
                 },
                 actualizar() {
                     const inicio = new Date(this.fechaIngreso.replace(' ', 'T'));
                     const ahora  = new Date();
                     const diff   = Math.max(0, Math.floor((ahora - inicio) / 60000));
                     const h = Math.floor(diff / 60);
                     const m = diff % 60;
                     this.tiempoTexto = h > 0 ? `${h}h ${m}min` : `${m} min`;
                 }
             }">

            {{-- Cabecera tarjeta --}}
            <div class="px-5 py-4 bg-indigo-50 dark:bg-indigo-900/30 flex items-center gap-4">
                <div class="h-11 w-11 rounded-full bg-indigo-100 dark:bg-indigo-900/60 flex items-center justify-center shrink-0">
                    <x-atoms.icono nombre="truck" tamaño="sm" class="text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-bold text-indigo-800 dark:text-indigo-300 truncate">
                        {{ $vehiculoEncontrado['NOMBRE_CLIENTE'] ?? $vehiculoEncontrado['nombre_cliente'] ?? '—' }}
                    </p>
                    <p class="text-xs text-indigo-500">
                        {{ $vehiculoEncontrado['TIPO_VEHICULO'] ?? $vehiculoEncontrado['tipo_vehiculo'] ?? '' }}
                        @if(!empty($vehiculoEncontrado['MARCA'])) · {{ $vehiculoEncontrado['MARCA'] }} @endif
                    </p>
                </div>
                <span class="text-xl font-black tracking-widest text-indigo-600 dark:text-indigo-300 shrink-0">
                    {{ $placa }}
                </span>
            </div>

            {{-- Datos --}}
            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                <div class="px-5 py-3 flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cupo</span>
                    <span class="text-sm font-bold text-gray-800 dark:text-white">{{ $registroActivo['codigo_cupo'] ?? '—' }}</span>
                </div>
                <div class="px-5 py-3 flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Hora de ingreso</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-slate-300">
                        @if($fechaIngreso)
                            {{ \Carbon\Carbon::parse($fechaIngreso)->format('d/m/Y H:i') }}
                        @else — @endif
                    </span>
                </div>
                <div class="px-5 py-3 flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Tiempo transcurrido</span>
                    <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 tabular-nums" x-text="tiempoTexto">—</span>
                </div>
                @if($tarifaEstimada)
                <div class="px-5 py-4 flex justify-between items-center bg-green-50 dark:bg-green-900/20">
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Tarifa estimada</span>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $tarifaEstimada['nombre_tarifa'] ?? '' }} · {{ $tarifaEstimada['minutos_transcurridos'] ?? 0 }} min</p>
                    </div>
                    <span class="text-2xl font-black text-green-600 dark:text-green-400">
                        ${{ number_format((float)($tarifaEstimada['total_cobrado'] ?? 0), 0, ',', '.') }}
                    </span>
                </div>
                @endif
            </div>

            {{-- Botón de salida --}}
            <div class="px-5 py-4 bg-gray-50 dark:bg-slate-700/50">
                <button wire:click="registrarSalida" wire:loading.attr="disabled" id="btn-registrar-salida"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-3 text-sm font-bold text-white shadow hover:bg-rose-500 active:bg-rose-700 transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="registrarSalida">
                        <x-atoms.icono nombre="arrow-left-circle" tamaño="sm" />
                    </span>
                    <span wire:loading wire:target="registrarSalida">
                        <x-atoms.icono nombre="arrow-path" tamaño="sm" class="animate-spin" />
                    </span>
                    <span wire:loading.remove wire:target="registrarSalida">Registrar Salida</span>
                    <span wire:loading wire:target="registrarSalida">Procesando...</span>
                </button>
            </div>
        </div>
        @endif

    </div>
    @endif

</div>

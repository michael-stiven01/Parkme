<div>
    <x-molecules.encabezado-modulo titulo="Registrar Ingreso" descripcion="Ingreso de vehículos al parqueadero" icono="arrow-right-circle" />

    {{-- Alertas flash --}}
    @if(session()->has('error'))
    <div class="mt-4 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 flex items-center gap-3">
        <x-atoms.icono nombre="x-circle" tamaño="sm" class="text-red-500 shrink-0" />
        <p class="text-sm font-medium text-red-700 dark:text-red-400">{{ session('error') }}</p>
    </div>
    @endif

    {{-- ── PANTALLA DE ÉXITO ──────────────────────────────────────────────── --}}
    @if($exito)
    <div class="mt-6 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800/50 p-8 text-center shadow-sm">
        <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
            <x-atoms.icono nombre="check-circle" tamaño="lg" class="text-green-600 dark:text-green-400" />
        </div>
        <h2 class="text-xl font-bold text-green-800 dark:text-green-300 mb-1">¡Ingreso Registrado!</h2>
        <p class="text-sm text-green-600 dark:text-green-400 mb-6">El vehículo ha ingresado correctamente al parqueadero.</p>

        <div class="mx-auto max-w-sm rounded-xl bg-white dark:bg-slate-800 border border-green-200 dark:border-green-800/40 divide-y divide-gray-100 dark:divide-slate-700 text-left shadow">
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Placa</span>
                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400 tracking-widest">{{ $resultadoExito['placa'] ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cliente</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ $resultadoExito['nombre_cliente'] ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cupo</span>
                <span class="text-sm font-bold text-gray-800 dark:text-white">{{ $resultadoExito['codigo_cupo'] ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Hora de ingreso</span>
                <span class="text-sm font-semibold text-gray-700 dark:text-slate-300">
                    @if(!empty($resultadoExito['fecha_ingreso']))
                        {{ \Carbon\Carbon::parse($resultadoExito['fecha_ingreso'])->format('d/m/Y H:i') }}
                    @else — @endif
                </span>
            </div>
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Tipo</span>
                <span class="text-sm text-gray-600 dark:text-slate-400">{{ $resultadoExito['tipo_vehiculo'] ?? '—' }}</span>
            </div>
        </div>

        <button wire:click="nuevoIngreso" id="btn-nuevo-ingreso"
            class="mt-6 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition-all">
            <x-atoms.icono nombre="plus" tamaño="sm" />
            Nuevo Ingreso
        </button>
    </div>
    @else

    {{-- ── TABS MODO ────────────────────────────────────────────────────────── --}}
    <div class="mt-6 border-b border-gray-200 dark:border-slate-700">
        <nav class="-mb-px flex space-x-1">
            <button wire:click="cambiarModo('rapido')" id="tab-ingreso-rapido"
                class="flex items-center gap-2 px-4 pb-3 text-sm font-semibold border-b-2 transition-colors
                    {{ $modo === 'rapido'
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 hover:border-gray-300' }}">
                <x-atoms.icono nombre="bolt" tamaño="sm" />
                Ingreso Rápido
            </button>
            <button wire:click="cambiarModo('primer_ingreso')" id="tab-primer-ingreso"
                class="flex items-center gap-2 px-4 pb-3 text-sm font-semibold border-b-2 transition-colors
                    {{ $modo === 'primer_ingreso'
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 hover:border-gray-300' }}">
                <x-atoms.icono nombre="user-plus" tamaño="sm" />
                Primer Ingreso
            </button>
        </nav>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODO B — INGRESO RÁPIDO                                               --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($modo === 'rapido')
    <div class="mt-8 max-w-xl mx-auto">
        {{-- Campo placa --}}
        <div>
            <label for="input-placa-rapido" class="block text-sm font-medium text-gray-900 dark:text-gray-300 mb-1">
                Placa del vehículo <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    type="text"
                    id="input-placa-rapido"
                    wire:model.live.debounce.400ms="placa_rapido"
                    maxlength="10"
                    style="text-transform:uppercase;letter-spacing:3px;"
                    placeholder="Ej: ABC123"
                    autocomplete="off"
                    class="block w-full rounded-xl border-0 py-3 pl-4 pr-12 text-lg font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-300 placeholder:font-normal placeholder:tracking-normal focus:ring-2 focus:ring-indigo-600 dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                >
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    @if($estadoVehiculo === 'disponible')
                        <x-atoms.icono nombre="check-circle" tamaño="sm" class="text-green-500" />
                    @elseif($estadoVehiculo === 'adentro')
                        <x-atoms.icono nombre="x-circle" tamaño="sm" class="text-red-500" />
                    @elseif($estadoVehiculo === 'no_encontrado')
                        <x-atoms.icono nombre="question-mark-circle" tamaño="sm" class="text-amber-500" />
                    @else
                        <x-atoms.icono nombre="magnifying-glass" tamaño="sm" class="text-gray-400" />
                    @endif
                </div>
            </div>
            <p class="mt-1 text-xs text-gray-400">Escribe la placa para buscar automáticamente.</p>
        </div>

        {{-- Estado: vehículo ya adentro --}}
        @if($estadoVehiculo === 'adentro')
        <div class="mt-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 flex items-start gap-3">
            <x-atoms.icono nombre="exclamation-triangle" tamaño="sm" class="text-red-500 shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-semibold text-red-700 dark:text-red-400">Vehículo ya se encuentra en el parqueadero</p>
                @if($fechaIngresoActual)
                <p class="text-xs text-red-500 mt-0.5">
                    Ingresó el {{ \Carbon\Carbon::parse($fechaIngresoActual)->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($fechaIngresoActual)->format('H:i') }}
                </p>
                @endif
            </div>
        </div>
        @endif

        {{-- Estado: no encontrado --}}
        @if($estadoVehiculo === 'no_encontrado')
        <div class="mt-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 flex items-start gap-3">
            <x-atoms.icono nombre="information-circle" tamaño="sm" class="text-amber-500 shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">Vehículo no registrado</p>
                <p class="text-xs text-amber-600 dark:text-amber-500 mt-0.5">
                    Esta placa no existe en el sistema. Usa la pestaña
                    <button wire:click="cambiarModo('primer_ingreso')" class="underline font-semibold">Primer Ingreso</button>
                    para registrarlo.
                </p>
            </div>
        </div>
        @endif

        {{-- Estado: disponible → tarjeta info + cupo --}}
        @if($estadoVehiculo === 'disponible' && $vehiculoEncontrado)
        <div class="mt-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800/50 p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="h-9 w-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                    <x-atoms.icono nombre="truck" tamaño="sm" class="text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <p class="text-sm font-bold text-indigo-800 dark:text-indigo-300">{{ $vehiculoEncontrado['NOMBRE_CLIENTE'] ?? ($vehiculoEncontrado['nombre_cliente'] ?? '—') }}</p>
                    <p class="text-xs text-indigo-500">{{ $vehiculoEncontrado['TIPO_VEHICULO'] ?? ($vehiculoEncontrado['tipo_vehiculo'] ?? '') }} · {{ $vehiculoEncontrado['MARCA'] ?? '' }}</p>
                </div>
            </div>

            {{-- Selector de cupo --}}
            <div class="mt-3">
                <label for="select-cupo-rapido" class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">
                    Seleccionar cupo <span class="text-red-500">*</span>
                </label>
                @if(count($cuposDisponibles) === 0)
                <p class="text-sm text-red-500 py-2">No hay cupos disponibles para este tipo de vehículo.</p>
                @else
                <select wire:model="id_cupo_rapido" id="select-cupo-rapido"
                    class="block w-full rounded-lg border-0 py-2 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-900 dark:text-white dark:ring-slate-700">
                    <option value="">-- Elige un cupo --</option>
                    @foreach($cuposDisponibles as $cupo)
                    <option value="{{ $cupo->id_cupo }}">
                        {{ $cupo->CODIGO }}{{ $cupo->TIPO_VEHICULO ? ' · '.$cupo->TIPO_VEHICULO : '' }}{{ $cupo->FILA ? ' (Fila '.$cupo->FILA.'-'.$cupo->COLUMNA.')' : '' }}
                    </option>
                    @endforeach
                </select>
                @error('id_cupo_rapido') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                @endif
            </div>

            @if(count($cuposDisponibles) > 0)
            <button wire:click="registrarIngresoRapido" wire:loading.attr="disabled" id="btn-registrar-rapido"
                class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition-all disabled:opacity-60">
                <span wire:loading.remove wire:target="registrarIngresoRapido">
                    <x-atoms.icono nombre="arrow-right-circle" tamaño="sm" />
                </span>
                <span wire:loading wire:target="registrarIngresoRapido">
                    <x-atoms.icono nombre="arrow-path" tamaño="sm" class="animate-spin" />
                </span>
                <span wire:loading.remove wire:target="registrarIngresoRapido">Registrar Ingreso</span>
                <span wire:loading wire:target="registrarIngresoRapido">Registrando...</span>
            </button>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODO A — PRIMER INGRESO                                               --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($modo === 'primer_ingreso')
    <div class="mt-8">
        <form wire:submit.prevent="registrarPrimerIngreso" class="space-y-8">

            {{-- SECCIÓN: Datos del cliente --}}
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800/50 p-6 shadow-sm">
                <h3 class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white mb-5">
                    <x-atoms.icono nombre="user" tamaño="sm" class="text-indigo-500" />
                    Datos del Cliente
                </h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Nombres --}}
                    <div>
                        <label for="input-nombres" class="block text-sm font-medium text-gray-900 dark:text-gray-300">Nombres <span class="text-red-500">*</span></label>
                        <input type="text" id="input-nombres" wire:model.defer="nombres" maxlength="100"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                            placeholder="Ej: Juan Carlos">
                        @error('nombres') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    {{-- Apellidos --}}
                    <div>
                        <label for="input-apellidos" class="block text-sm font-medium text-gray-900 dark:text-gray-300">Apellidos <span class="text-red-500">*</span></label>
                        <input type="text" id="input-apellidos" wire:model.defer="apellidos" maxlength="100"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                            placeholder="Ej: Pérez González">
                        @error('apellidos') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    {{-- Correo --}}
                    <div>
                        <label for="input-correo" class="block text-sm font-medium text-gray-900 dark:text-gray-300">Correo electrónico <span class="text-red-500">*</span></label>
                        <input type="email" id="input-correo" wire:model.defer="correo" maxlength="150"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                            placeholder="correo@ejemplo.com">
                        @error('correo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    {{-- Teléfono --}}
                    <div>
                        <label for="input-telefono" class="block text-sm font-medium text-gray-900 dark:text-gray-300">Teléfono</label>
                        <input type="text" id="input-telefono" wire:model.defer="telefono" maxlength="20"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                            placeholder="Ej: 3001234567">
                        @error('telefono') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- SECCIÓN: Datos del vehículo --}}
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800/50 p-6 shadow-sm">
                <h3 class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white mb-5">
                    <x-atoms.icono nombre="truck" tamaño="sm" class="text-indigo-500" />
                    Datos del Vehículo
                </h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Placa --}}
                    <div>
                        <label for="input-placa-nuevo" class="block text-sm font-medium text-gray-900 dark:text-gray-300">
                            Placa <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="input-placa-nuevo"
                            wire:model.live.debounce.500ms="placa"
                            maxlength="10"
                            style="text-transform:uppercase;letter-spacing:2px;"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 placeholder:font-normal placeholder:tracking-normal focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                            placeholder="ABC123">
                        @error('placa') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                        {{-- Alerta placa ya existe --}}
                        @if($alertaPlaca === 'ya_existe')
                        <div class="mt-1.5 flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                            <x-atoms.icono nombre="exclamation-triangle" tamaño="sm" class="shrink-0" />
                            <p class="text-xs font-medium">Esta placa ya está registrada. Usa
                                <button type="button" wire:click="cambiarModo('rapido')" class="underline font-semibold">Ingreso Rápido</button>.
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- Tipo de vehículo --}}
                    <div>
                        <label for="select-tipo-ingreso" class="block text-sm font-medium text-gray-900 dark:text-gray-300">
                            Tipo de vehículo <span class="text-red-500">*</span>
                        </label>
                        <select id="select-tipo-ingreso" wire:model.live="id_tipo_vehiculo"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700">
                            <option value="">-- Selecciona --</option>
                            @foreach($tiposVehiculo as $tipo)
                            <option value="{{ $tipo->id_tipo_vehiculo }}">{{ $tipo->NOMBRE }}</option>
                            @endforeach
                        </select>
                        @error('id_tipo_vehiculo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Marca --}}
                    <div>
                        <label for="input-marca" class="block text-sm font-medium text-gray-900 dark:text-gray-300">Marca</label>
                        <input type="text" id="input-marca" wire:model.defer="marca" maxlength="80"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                            placeholder="Ej: Chevrolet">
                    </div>

                    {{-- Cupo --}}
                    <div>
                        <label for="select-cupo-nuevo" class="block text-sm font-medium text-gray-900 dark:text-gray-300">
                            Cupo <span class="text-red-500">*</span>
                        </label>
                        <select id="select-cupo-nuevo" wire:model.defer="id_cupo"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                            @if(!$id_tipo_vehiculo) disabled @endif>
                            <option value="">@if(!$id_tipo_vehiculo) Selecciona primero el tipo @else -- Elige un cupo -- @endif</option>
                            @foreach($cuposDisponibles as $cupo)
                            <option value="{{ $cupo->id_cupo }}">
                                {{ $cupo->CODIGO }}{{ $cupo->FILA ? ' (Fila '.$cupo->FILA.'-'.$cupo->COLUMNA.')' : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_cupo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        @if($id_tipo_vehiculo && count($cuposDisponibles) === 0)
                        <p class="mt-1 text-xs text-red-500">Sin cupos disponibles para este tipo de vehículo.</p>
                        @endif
                    </div>

                    {{-- Descripción (span full) --}}
                    <div class="sm:col-span-2">
                        <label for="input-descripcion" class="block text-sm font-medium text-gray-900 dark:text-gray-300">Descripción / Color</label>
                        <input type="text" id="input-descripcion" wire:model.defer="descripcion" maxlength="255"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                            placeholder="Ej: Rojo, 4 puertas">
                    </div>
                </div>
            </div>

            {{-- Botón submit --}}
            <div class="flex justify-end">
                <button type="submit" id="btn-registrar-primer-ingreso"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="registrarPrimerIngreso">
                        <x-atoms.icono nombre="arrow-right-circle" tamaño="sm" />
                    </span>
                    <span wire:loading wire:target="registrarPrimerIngreso">
                        <x-atoms.icono nombre="arrow-path" tamaño="sm" class="animate-spin" />
                    </span>
                    <span wire:loading.remove wire:target="registrarPrimerIngreso">Registrar Ingreso</span>
                    <span wire:loading wire:target="registrarPrimerIngreso">Registrando...</span>
                </button>
            </div>

        </form>
    </div>
    @endif

    @endif {{-- /exito --}}
</div>

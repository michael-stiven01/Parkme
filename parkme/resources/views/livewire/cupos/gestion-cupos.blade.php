<div wire:poll.30000ms="refrescarMapa">
    <x-molecules.encabezado-modulo titulo="Cupos" descripcion="Mapa y gestión de cupos del parqueadero" icono="squares-2x2">
        @if(strtolower(auth()->user()->ROL) === 'administrador')
        <button wire:click="abrirModalCrear" id="btn-nuevo-cupo" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
            <x-atoms.icono nombre="plus" tamaño="sm" />
            Nuevo Cupo
        </button>
        @endif
    </x-molecules.encabezado-modulo>

    @if(session()->has('success'))
    <div class="mt-4 rounded-md bg-green-50 p-4 border border-green-200 dark:bg-green-900/30 dark:border-green-800">
        <div class="flex">
            <x-atoms.icono nombre="check-circle" tamaño="sm" class="text-green-400 shrink-0" />
            <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-400">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- Contadores --}}
    <div class="mt-6 grid grid-cols-3 gap-4">
        <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/50 p-4 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-green-500/20 flex items-center justify-center">
                <x-atoms.icono nombre="check-circle" tamaño="sm" class="text-green-600 dark:text-green-400" />
            </div>
            <div>
                <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $totalDisponibles }}</p>
                <p class="text-xs text-green-600 dark:text-green-500 font-medium">Disponibles</p>
            </div>
        </div>
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 p-4 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-red-500/20 flex items-center justify-center">
                <x-atoms.icono nombre="x-circle" tamaño="sm" class="text-red-600 dark:text-red-400" />
            </div>
            <div>
                <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $totalOcupados }}</p>
                <p class="text-xs text-red-600 dark:text-red-500 font-medium">Ocupados</p>
            </div>
        </div>
        <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 p-4 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-amber-500/20 flex items-center justify-center">
                <x-atoms.icono nombre="wrench-screwdriver" tamaño="sm" class="text-amber-600 dark:text-amber-400" />
            </div>
            <div>
                <p class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $totalMantenimiento }}</p>
                <p class="text-xs text-amber-600 dark:text-amber-500 font-medium">Mantenimiento</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mt-6 border-b border-gray-200 dark:border-slate-700">
        <nav class="-mb-px flex space-x-6">
            <button wire:click="cambiarVista('mapa')" id="tab-mapa"
                class="pb-3 text-sm font-semibold border-b-2 transition-colors {{ $vista === 'mapa' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 hover:border-gray-300' }}">
                <span class="flex items-center gap-2">
                    <x-atoms.icono nombre="squares-2x2" tamaño="sm" /> Mapa Visual
                </span>
            </button>
            <button wire:click="cambiarVista('lista')" id="tab-lista"
                class="pb-3 text-sm font-semibold border-b-2 transition-colors {{ $vista === 'lista' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 hover:border-gray-300' }}">
                <span class="flex items-center gap-2">
                    <x-atoms.icono nombre="list-bullet" tamaño="sm" /> Lista
                </span>
            </button>
        </nav>
    </div>

    {{-- ── MAPA ─────────────────────────────────────────────────────────── --}}
    @if($vista === 'mapa')
    <div class="mt-6">
        {{-- Leyenda --}}
        <div class="mb-4 flex flex-wrap gap-4 text-xs font-medium">
            <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-full bg-green-500"></span> Disponible</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-full bg-red-500"></span> Ocupado</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-full bg-amber-400"></span> Mantenimiento</span>
        </div>

        @if(count($mapa) === 0)
        <div class="py-20 text-center text-gray-400 dark:text-slate-500">
            <x-atoms.icono nombre="squares-2x2" tamaño="lg" class="mx-auto mb-2 opacity-40" />
            <p class="text-sm">No hay cupos registrados. Crea el primero usando "Nuevo Cupo".</p>
        </div>
        @else
        @php
            $filas = collect($mapa)->groupBy('FILA')->sortKeys();
        @endphp
        <div class="space-y-4">
            @foreach($filas as $fila => $cuposFila)
            <div>
                @if($fila)
                <div class="mb-2 text-xs font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase">Fila {{ $fila }}</div>
                @endif
                <div class="flex flex-wrap gap-3">
                    @foreach($cuposFila->sortBy('COLUMNA') as $cupo)
                    @php
                        $bg = match($cupo->ESTADO) {
                            'disponible'   => 'bg-green-500/10 border-green-500/40 hover:bg-green-500/20',
                            'ocupado'      => 'bg-red-500/10 border-red-500/40 hover:bg-red-500/20',
                            'mantenimiento'=> 'bg-amber-400/10 border-amber-400/40 hover:bg-amber-400/20',
                            default        => 'bg-gray-100 border-gray-300',
                        };
                        $dot = match($cupo->ESTADO) {
                            'disponible'    => 'bg-green-500',
                            'ocupado'       => 'bg-red-500',
                            'mantenimiento' => 'bg-amber-400',
                            default         => 'bg-gray-400',
                        };
                        $label = match($cupo->ESTADO) {
                            'disponible'    => 'Disponible',
                            'ocupado'       => 'Ocupado',
                            'mantenimiento' => 'Mantenimiento',
                            default         => $cupo->ESTADO,
                        };
                    @endphp
                    <div class="relative group">
                        <div class="w-24 h-24 rounded-xl border-2 {{ $bg }} flex flex-col items-center justify-center cursor-default transition-all duration-200 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full {{ $dot }} mb-1.5"></span>
                            <span class="text-xs font-bold text-gray-800 dark:text-white leading-tight text-center px-1">{{ $cupo->CODIGO }}</span>
                            @if($cupo->TIPO_VEHICULO)
                            <span class="mt-1 text-[10px] text-gray-500 dark:text-slate-400 text-center leading-tight px-1">{{ $cupo->TIPO_VEHICULO }}</span>
                            @endif
                        </div>
                        {{-- Tooltip --}}
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-50 pointer-events-none">
                            <div class="bg-gray-900 dark:bg-slate-700 text-white text-xs rounded-lg px-3 py-2 shadow-xl whitespace-nowrap">
                                <p class="font-bold">{{ $cupo->CODIGO }}</p>
                                <p class="text-gray-300">{{ $cupo->TIPO_VEHICULO ?? 'Cualquier tipo' }}</p>
                                <p class="mt-0.5">Estado: <span class="font-semibold">{{ $label }}</span></p>
                                @if($cupo->PLACA_ACTUAL)
                                <p class="mt-0.5 text-yellow-300">Placa: {{ $cupo->PLACA_ACTUAL }}</p>
                                @endif
                                @if($cupo->FILA || $cupo->COLUMNA)
                                <p class="text-gray-400 mt-0.5">Pos: {{ $cupo->FILA ?? '—' }}-{{ $cupo->COLUMNA ?? '—' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- ── LISTA ────────────────────────────────────────────────────────── --}}
    @if($vista === 'lista')
    <div class="mt-6">
        <div class="mb-4">
            <x-molecules.buscador wire_model="busqueda" placeholder="Buscar por código, tipo o fila..." />
        </div>

        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-slate-700/50">
                <thead class="bg-gray-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Código</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Tipo de Vehículo</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Fila</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Columna</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Estado</th>
                        @if(strtolower(auth()->user()->ROL) === 'administrador')
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Acciones</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-700/50 bg-white dark:bg-slate-900/50">
                    @forelse($cuposFiltrados as $cupo)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-gray-900 dark:text-white sm:pl-6">
                            {{ $cupo->CODIGO }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                            {{ $cupo->TIPO_VEHICULO ?? 'Cualquiera' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                            {{ $cupo->FILA ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                            {{ $cupo->COLUMNA ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($cupo->ESTADO === 'disponible')
                                <span class="inline-flex items-center gap-1 rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-400 dark:ring-green-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Disponible
                                </span>
                            @elseif($cupo->ESTADO === 'ocupado')
                                <span class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Ocupado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400 dark:ring-amber-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span> Mantenimiento
                                </span>
                            @endif
                        </td>
                        @if(strtolower(auth()->user()->ROL) === 'administrador')
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-3">
                            <button wire:click="abrirModalEditar({{ $cupo->id_cupo }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Editar
                            </button>
                            @if($cupo->ESTADO !== 'ocupado')
                                @if($cupo->ESTADO === 'disponible')
                                    <button wire:click="confirmarCambioEstado({{ $cupo->id_cupo }}, '{{ $cupo->ESTADO }}')" class="text-amber-600 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-300">
                                        Mantenimiento
                                    </button>
                                @else
                                    <button wire:click="confirmarCambioEstado({{ $cupo->id_cupo }}, '{{ $cupo->ESTADO }}')" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                        Reactivar
                                    </button>
                                @endif
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-sm text-gray-500 dark:text-slate-400">
                            No se encontraron cupos.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── MODAL FORMULARIO ─────────────────────────────────────────────── --}}
    @if($modalVisible)
    <div class="relative z-40" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-slate-900/80"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-lg bg-white dark:bg-slate-800 p-6 shadow-xl border border-gray-200 dark:border-slate-700">
                    <div class="absolute right-4 top-4">
                        <button wire:click="cerrarModal" class="rounded-md text-gray-400 hover:text-gray-500">
                            <x-atoms.icono nombre="x-mark" tamaño="md" />
                        </button>
                    </div>
                    <div class="flex items-center gap-3 mb-5">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                            <x-atoms.icono nombre="squares-2x2" tamaño="sm" class="text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $modoEdicion ? 'Editar Cupo' : 'Nuevo Cupo' }}
                        </h3>
                    </div>

                    <form wire:submit.prevent="guardar" class="space-y-4">
                        {{-- Código --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-300">Código <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="codigo"
                                id="input-codigo-cupo"
                                maxlength="20"
                                style="text-transform:uppercase"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                                placeholder="Ej: A-01">
                            @error('codigo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tipo Vehículo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-300">Tipo de Vehículo</label>
                            <select wire:model.defer="id_tipo_vehiculo"
                                id="select-tipo-vehiculo-cupo"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700">
                                <option value="">Cualquier tipo</option>
                                @foreach($tiposVehiculo as $tipo)
                                    <option value="{{ $tipo->id_tipo_vehiculo }}">{{ $tipo->NOMBRE }}</option>
                                @endforeach
                            </select>
                            @error('id_tipo_vehiculo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Fila y Columna --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-gray-300">Fila</label>
                                <input type="text" wire:model.defer="fila"
                                    id="input-fila-cupo"
                                    maxlength="5"
                                    style="text-transform:uppercase"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                                    placeholder="Ej: A">
                                @error('fila') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-gray-300">Columna</label>
                                <input type="number" wire:model.defer="columna"
                                    id="input-columna-cupo"
                                    min="1" max="999"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700"
                                    placeholder="Ej: 1">
                                @error('columna') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Estado (solo creación) --}}
                        @if(!$modoEdicion)
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-300">Estado inicial <span class="text-red-500">*</span></label>
                            <select wire:model.defer="estado"
                                id="select-estado-cupo"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm dark:bg-slate-900 dark:text-white dark:ring-slate-700">
                                <option value="disponible">Disponible</option>
                                <option value="mantenimiento">Mantenimiento</option>
                            </select>
                            @error('estado') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="mt-5 flex flex-row-reverse gap-3">
                            <button type="submit" id="btn-guardar-cupo" class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Guardar
                            </button>
                            <button wire:click="cerrarModal" type="button" class="inline-flex justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-slate-700 dark:text-white dark:ring-slate-600">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── MODAL CONFIRMACIÓN ESTADO ────────────────────────────────────── --}}
    @if($modalConfirmacionVisible)
    <div class="relative z-50" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-slate-900/80"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-lg bg-white dark:bg-slate-800 shadow-xl border border-gray-200 dark:border-slate-700">
                    <div class="px-6 py-5">
                        <div class="flex items-start gap-4">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center shrink-0 {{ $estadoObjetivo === 'mantenimiento' ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-green-100 dark:bg-green-900/30' }}">
                                <x-atoms.icono nombre="exclamation-triangle" tamaño="sm" class="{{ $estadoObjetivo === 'mantenimiento' ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}" />
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $estadoObjetivo === 'mantenimiento' ? 'Poner en Mantenimiento' : 'Reactivar Cupo' }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                                    ¿Confirmas {{ $estadoObjetivo === 'mantenimiento' ? 'poner en mantenimiento' : 'reactivar' }} el cupo
                                    <strong>{{ $cupoSeleccionado->CODIGO ?? '' }}</strong>?
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-700/50 px-6 py-3 flex flex-row-reverse gap-3 rounded-b-lg">
                        <button wire:click="cambiarEstado" id="btn-confirmar-estado-cupo"
                            class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm {{ $estadoObjetivo === 'mantenimiento' ? 'bg-amber-500 hover:bg-amber-400' : 'bg-green-600 hover:bg-green-500' }}">
                            Confirmar
                        </button>
                        <button wire:click="cerrarModal" type="button" class="inline-flex justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div>
    <x-molecules.encabezado-modulo 
        titulo="Tarifas" 
        descripcion="Gestiona las tarifas de cobro para tu parqueadero" 
        icono="currency-dollar">
        <button wire:click="abrirModalCrear" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
            <x-atoms.icono nombre="plus" tamaño="sm" />
            Nueva Tarifa
        </button>
    </x-molecules.encabezado-modulo>

    <div class="mt-8">
        @if (session()->has('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200 dark:bg-green-900/30 dark:border-green-800">
                <div class="flex">
                    <div class="shrink-0">
                        <x-atoms.icono nombre="check-circle" tamaño="sm" class="text-green-400 dark:text-green-500" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-400">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-4 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-slate-700/50">
                            <thead class="bg-gray-50 dark:bg-slate-800/50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Nombre</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Tipo de vehículo</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Tipo de tarifa</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Valor (COP)</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Horario especial</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Estado</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-slate-700/50 bg-white dark:bg-slate-900/50">
                                @forelse($tarifas as $tarifa)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                            {{ $tarifa->NOMBRE }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            @if($tarifa->TIPO_VEHICULO)
                                                {{ $tarifa->TIPO_VEHICULO }}
                                            @else
                                                <span class="italic text-gray-400">Todos</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            @if($tarifa->TIPO_TARIFA === 'por_hora')
                                                Por Hora
                                            @elseif($tarifa->TIPO_TARIFA === 'por_fraccion')
                                                Por Fracción ({{ $tarifa->FRACCION_MINUTOS }} min)
                                            @elseif($tarifa->TIPO_TARIFA === 'plana_dia')
                                                Plana por Día
                                            @elseif($tarifa->TIPO_TARIFA === 'especial')
                                                Especial
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                            $ {{ number_format($tarifa->VALOR, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            @if($tarifa->TIPO_TARIFA === 'especial' && $tarifa->HORA_INICIO && $tarifa->HORA_FIN)
                                                {{ substr($tarifa->HORA_INICIO, 0, 5) }} - {{ substr($tarifa->HORA_FIN, 0, 5) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if($tarifa->ACTIVO == 1)
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-400 dark:ring-green-800">Activo</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button wire:click="abrirModalEditar({{ $tarifa->id_tarifa }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4">Editar</button>
                                            
                                            @if($tarifa->ACTIVO == 1)
                                                <button wire:click="confirmarCambioEstado({{ $tarifa->id_tarifa }}, {{ $tarifa->ACTIVO }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Desactivar</button>
                                            @else
                                                <button wire:click="confirmarCambioEstado({{ $tarifa->id_tarifa }}, {{ $tarifa->ACTIVO }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Activar</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-10 text-center text-sm text-gray-500 dark:text-slate-400">
                                            No se encontraron tarifas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Formulario Tarifa -->
    @if($modalVisible)
    <div class="relative z-40" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-slate-900/80 transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6 border border-gray-200 dark:border-slate-700">
                    <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                        <button wire:click="cerrarModal" type="button" class="rounded-md bg-white dark:bg-slate-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                            <span class="sr-only">Cerrar</span>
                            <x-atoms.icono nombre="x-mark" tamaño="md" />
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <x-atoms.icono nombre="currency-dollar" tamaño="sm" class="text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                {{ $modoEdicion ? 'Editar Tarifa' : 'Nueva Tarifa' }}
                            </h3>
                            <div class="mt-2">
                                <form wire:submit.prevent="guardar" class="mt-4 space-y-4">
                                    
                                    <div>
                                        <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Nombre de tarifa <span class="text-red-500">*</span></label>
                                        <div class="mt-2">
                                            <input type="text" wire:model.defer="nombre" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700" placeholder="Ej: Hora Carro" required>
                                        </div>
                                        @error('nombre') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Tipo de Vehículo</label>
                                            <div class="mt-2">
                                                <select wire:model.defer="id_tipo_vehiculo" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700">
                                                    <option value="">Aplica a TODOS</option>
                                                    @foreach($tiposVehiculo as $tipo)
                                                        <option value="{{ $tipo->id_tipo_vehiculo }}">{{ $tipo->NOMBRE }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('id_tipo_vehiculo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Tipo de Tarifa <span class="text-red-500">*</span></label>
                                            <div class="mt-2">
                                                <select wire:model.live="tipo_tarifa" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="por_hora">Por Hora</option>
                                                    <option value="por_fraccion">Por Fracción de Tiempo</option>
                                                    <option value="plana_dia">Plana por Día</option>
                                                    <option value="especial">Especial (Horario)</option>
                                                </select>
                                            </div>
                                            @error('tipo_tarifa') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Valor (COP) <span class="text-red-500">*</span></label>
                                        <div class="mt-2 relative rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                              <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" wire:model.defer="valor" class="block w-full rounded-md border-0 py-1.5 pl-7 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700" placeholder="0.00" step="0.01" min="0" required>
                                        </div>
                                        @error('valor') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    @if($tipo_tarifa === 'por_fraccion')
                                        <div>
                                            <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Fracción (Minutos) <span class="text-red-500">*</span></label>
                                            <div class="mt-2">
                                                <input type="number" wire:model.defer="fraccion_minutos" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700" min="1" placeholder="Ej: 15" required>
                                            </div>
                                            @error('fraccion_minutos') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                    @if($tipo_tarifa === 'especial')
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Hora Inicio <span class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <input type="time" wire:model.defer="hora_inicio" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700" required>
                                                </div>
                                                @error('hora_inicio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Hora Fin <span class="text-red-500">*</span></label>
                                                <div class="mt-2">
                                                    <input type="time" wire:model.defer="hora_fin" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700" required>
                                                </div>
                                                @error('hora_fin') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500 dark:text-slate-400">Si la tarifa especial cruza la medianoche (ej: 22:00 a 06:00), asegúrate de ingresar ambas horas correctamente.</p>
                                    @endif
                                    
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">
                                            Guardar
                                        </button>
                                        <button wire:click="cerrarModal" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-slate-700 dark:text-white dark:ring-slate-600 dark:hover:bg-slate-600">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Confirmación -->
    @if($modalConfirmacionVisible)
    <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-slate-900/80 transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200 dark:border-slate-700">
                    <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10 {{ $estadoObjetivo == 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-green-100 dark:bg-green-900/30' }}">
                                <x-atoms.icono nombre="exclamation-triangle" tamaño="sm" class="{{ $estadoObjetivo == 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}" />
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                    {{ $estadoObjetivo == 0 ? 'Desactivar Tarifa' : 'Activar Tarifa' }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-slate-400">
                                        ¿Estás seguro de que deseas {{ $estadoObjetivo == 0 ? 'desactivar' : 'activar' }} la tarifa <strong>{{ $tarifaSeleccionada->NOMBRE }}</strong>? 
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button wire:click="cambiarEstado" type="button" class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto {{ $estadoObjetivo == 0 ? 'bg-red-600 hover:bg-red-500' : 'bg-green-600 hover:bg-green-500' }}">
                            Confirmar
                        </button>
                        <button wire:click="cerrarModal" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-slate-800 dark:text-white dark:ring-slate-600 dark:hover:bg-slate-700">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

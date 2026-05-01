<div>
    <x-molecules.encabezado-modulo 
        titulo="Vehículos" 
        descripcion="Gestiona los vehículos registrados en tu parqueadero" 
        icono="truck">
        @if(strtolower(auth()->user()->ROL) !== 'portero')
        <button wire:click="abrirModalCrear" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
            <x-atoms.icono nombre="plus" tamaño="sm" />
            Nuevo Vehículo
        </button>
        @endif
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

        @if (session()->has('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200 dark:bg-red-900/30 dark:border-red-800">
                <div class="flex">
                    <div class="shrink-0">
                        <x-atoms.icono nombre="x-circle" tamaño="sm" class="text-red-400 dark:text-red-500" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-400">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div class="w-full sm:max-w-xs">
                <label for="busqueda" class="sr-only">Buscar</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-atoms.icono nombre="magnifying-glass" tamaño="sm" class="text-gray-400 dark:text-gray-500" />
                    </div>
                    <input wire:model.live.debounce.300ms="busqueda" type="text" id="busqueda" class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:placeholder:text-slate-400" placeholder="Buscar por placa o cliente...">
                </div>
            </div>
        </div>

        <div class="mt-4 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-slate-700/50">
                            <thead class="bg-gray-50 dark:bg-slate-800/50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Placa</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Tipo</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Marca</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Cliente</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Estado</th>
                                    @if(strtolower(auth()->user()->ROL) !== 'portero')
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-slate-700/50 bg-white dark:bg-slate-900/50">
                                @forelse($vehiculos as $vehiculo)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-bold text-yellow-800 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-400 dark:ring-yellow-800 border-x-2 border-x-gray-400 dark:border-x-gray-500 uppercase tracking-widest">
                                                {{ $vehiculo->PLACA }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            <div class="flex items-center gap-2">
                                                @if($vehiculo->ICONO_TIPO)
                                                    <x-atoms.icono nombre="{{ $vehiculo->ICONO_TIPO }}" tamaño="sm" />
                                                @endif
                                                {{ $vehiculo->TIPO_VEHICULO }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            {{ $vehiculo->MARCA ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900 dark:text-gray-300">{{ $vehiculo->NOMBRE_CLIENTE }}</span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if($vehiculo->ACTIVO == 1)
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-400 dark:ring-green-800">Activo</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800">Inactivo</span>
                                            @endif
                                        </td>
                                        @if(strtolower(auth()->user()->ROL) !== 'portero')
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button wire:click="abrirModalEditar({{ $vehiculo->id_vehiculo }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4">Editar<span class="sr-only">, {{ $vehiculo->PLACA }}</span></button>
                                            
                                            @if($vehiculo->ACTIVO == 1)
                                                <button wire:click="confirmarCambioEstado({{ $vehiculo->id_vehiculo }}, {{ $vehiculo->ACTIVO }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Desactivar</button>
                                            @else
                                                <button wire:click="confirmarCambioEstado({{ $vehiculo->id_vehiculo }}, {{ $vehiculo->ACTIVO }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Activar</button>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ strtolower(auth()->user()->ROL) !== 'portero' ? '6' : '5' }}" class="py-10 text-center text-sm text-gray-500 dark:text-slate-400">
                                            No se encontraron vehículos.
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

    <!-- Modal Formulario Vehículo -->
    @if($modalVisible && strtolower(auth()->user()->ROL) !== 'portero')
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
                            <x-atoms.icono nombre="truck" tamaño="sm" class="text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                {{ $modoEdicion ? 'Editar Vehículo' : 'Nuevo Vehículo' }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-slate-400">
                                    {{ $modoEdicion ? 'Modifica los datos del vehículo.' : 'Ingresa los datos para registrar un nuevo vehículo.' }}
                                </p>
                                
                                <form wire:submit.prevent="guardar" class="mt-4 space-y-4">
                                    <!-- Cliente Selection -->
                                    <div>
                                        <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Cliente Propietario <span class="text-red-500">*</span></label>
                                        @if($modoEdicion)
                                            <div class="mt-2">
                                                <input type="text" value="{{ $nombre_cliente }}" class="block w-full rounded-md border-0 py-1.5 text-gray-500 bg-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700 cursor-not-allowed" disabled>
                                            </div>
                                        @else
                                            <div class="mt-2 flex rounded-md shadow-sm">
                                                <div class="relative flex flex-grow items-stretch focus-within:z-10">
                                                    <input type="text" readonly value="{{ $nombre_cliente }}" class="block w-full rounded-none rounded-l-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700 {{ empty($nombre_cliente) ? 'bg-gray-50 dark:bg-slate-800 text-gray-500' : '' }}" placeholder="Seleccione un cliente..." required>
                                                </div>
                                                <button type="button" wire:click="abrirModalClientes" class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:hover:bg-slate-700">
                                                    <x-atoms.icono nombre="magnifying-glass" tamaño="sm" class="-ml-0.5 text-gray-400" />
                                                    Buscar
                                                </button>
                                            </div>
                                            @error('id_cliente') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <!-- Placa -->
                                        <div>
                                            <label for="placa" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Placa <span class="text-red-500">*</span></label>
                                            <div class="mt-2">
                                                <input type="text" wire:model.live="placa" id="placa" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700 uppercase {{ $modoEdicion ? 'bg-gray-100 text-gray-500 dark:bg-slate-800 dark:text-slate-400 cursor-not-allowed' : '' }}" maxlength="10" required {{ $modoEdicion ? 'disabled' : '' }}>
                                            </div>
                                            @error('placa') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Tipo Vehículo -->
                                        <div>
                                            <label for="id_tipo_vehiculo" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Tipo de Vehículo <span class="text-red-500">*</span></label>
                                            <div class="mt-2">
                                                <select wire:model.defer="id_tipo_vehiculo" id="id_tipo_vehiculo" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700 {{ $modoEdicion ? 'bg-gray-100 text-gray-500 dark:bg-slate-800 dark:text-slate-400 cursor-not-allowed' : '' }}" required {{ $modoEdicion ? 'disabled' : '' }}>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($tiposVehiculo as $tipo)
                                                        <option value="{{ $tipo->id_tipo_vehiculo }}">{{ $tipo->NOMBRE }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('id_tipo_vehiculo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="marca" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Marca / Modelo</label>
                                        <div class="mt-2">
                                            <input type="text" wire:model.defer="marca" id="marca" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700 {{ $modoEdicion ? 'bg-gray-100 text-gray-500 dark:bg-slate-800 dark:text-slate-400 cursor-not-allowed' : '' }}" maxlength="80" {{ $modoEdicion ? 'disabled' : '' }}>
                                        </div>
                                        @error('marca') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="descripcion" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">Descripción / Detalles Adicionales</label>
                                        <div class="mt-2">
                                            <textarea wire:model.defer="descripcion" id="descripcion" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700" maxlength="255"></textarea>
                                        </div>
                                        @error('descripcion') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    
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

    <!-- Modal Selección de Cliente -->
    @if($modalClientesVisible)
    <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-slate-900/80 transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6 border border-gray-200 dark:border-slate-700">
                    <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                        <button wire:click="cerrarModalClientes" type="button" class="rounded-md bg-white dark:bg-slate-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                            <span class="sr-only">Cerrar</span>
                            <x-atoms.icono nombre="x-mark" tamaño="md" />
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start w-full">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <x-atoms.icono nombre="user-group" tamaño="sm" class="text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                Seleccionar Cliente
                            </h3>
                            <div class="mt-2 w-full">
                                <div class="relative mt-2">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <x-atoms.icono nombre="magnifying-glass" tamaño="sm" class="text-gray-400 dark:text-gray-500" />
                                    </div>
                                    <input wire:model.live.debounce.300ms="busquedaCliente" type="text" class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700 dark:placeholder:text-slate-400" placeholder="Buscar por nombre o documento...">
                                </div>

                                <div class="mt-4 border border-gray-200 dark:border-slate-700 rounded-md overflow-hidden bg-white dark:bg-slate-900 max-h-64 overflow-y-auto">
                                    <ul role="list" class="divide-y divide-gray-200 dark:divide-slate-700">
                                        @forelse($clientes as $cliente)
                                            <li class="p-4 hover:bg-gray-50 dark:hover:bg-slate-800 cursor-pointer transition-colors" wire:click="seleccionarCliente({{ $cliente->id_cliente }}, '{{ addslashes($cliente->NOMBRES . ' ' . $cliente->APELLIDOS) }}', '{{ addslashes($cliente->CORREO) }}')">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $cliente->NOMBRES }} {{ $cliente->APELLIDOS }}</p>
                                                        <p class="text-sm text-gray-500 dark:text-slate-400">{{ $cliente->CORREO }} | {{ $cliente->TELEFONO ?? 'Sin teléfono' }}</p>
                                                    </div>
                                                    <div class="ml-4 flex-shrink-0">
                                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/20 dark:bg-indigo-900/30 dark:text-indigo-400 dark:ring-indigo-800">
                                                            Seleccionar
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="p-4 text-center text-sm text-gray-500 dark:text-slate-400">
                                                @if(strlen($busquedaCliente) >= 2)
                                                    No se encontraron clientes con esa búsqueda.
                                                @else
                                                    Escribe al menos 2 caracteres para buscar clientes.
                                                @endif
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-200 dark:border-slate-700 mt-4 rounded-b-lg">
                        <button wire:click="cerrarModalClientes" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-slate-800 dark:text-white dark:ring-slate-600 dark:hover:bg-slate-700">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Confirmación -->
    @if($modalConfirmacionVisible && strtolower(auth()->user()->ROL) !== 'portero')
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
                                    {{ $estadoObjetivo == 0 ? 'Desactivar Vehículo' : 'Activar Vehículo' }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-slate-400">
                                        ¿Estás seguro de que deseas {{ $estadoObjetivo == 0 ? 'desactivar' : 'activar' }} el vehículo con placa <strong>{{ $vehiculoSeleccionado->PLACA }}</strong>? 
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

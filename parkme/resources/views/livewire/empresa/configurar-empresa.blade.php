<div>
    <x-molecules.encabezado-modulo 
        titulo="Configuración de la Empresa" 
        descripcion="Administra la información de tu parqueadero." 
        icono="building-office"
    />

    @if (session()->has('success'))
        <div class="p-4 mb-6 text-sm text-emerald-800 rounded-lg bg-emerald-50 dark:bg-gray-800 dark:text-emerald-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <form wire:submit.prevent="actualizar" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- NIT (Read-only) -->
                <div>
                    <label for="nit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIT <span class="text-xs text-gray-500 font-normal">(No editable)</span></label>
                    <input wire:model="nit" type="text" id="nit" disabled class="block w-full px-4 py-3 mt-1 bg-gray-100 border border-gray-300 rounded-lg shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed">
                </div>

                <!-- Tipo -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Parqueadero</label>
                    <select wire:model.live="tipo" id="tipo" class="block w-full px-4 py-3 mt-1 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white transition-colors">
                        <option value="">Selecciona un tipo...</option>
                        <option value="Público">Público</option>
                        <option value="Privado">Privado</option>
                        <option value="Comercial">Comercial</option>
                        <option value="Residencial">Residencial</option>
                        <option value="Mixto">Mixto</option>
                    </select>
                    @error('tipo') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Nombre -->
                <div class="md:col-span-2">
                    <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Empresa / Parqueadero</label>
                    <div class="relative mt-1">
                        <input wire:model.live="nombre" type="text" id="nombre" maxlength="150" class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white transition-colors" placeholder="Ej: Parking Center">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-xs text-gray-400">{{ strlen($nombre) }}/150</span>
                        </div>
                    </div>
                    @error('nombre') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Dirección -->
                <div class="md:col-span-2">
                    <label for="direccion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                    <div class="relative mt-1">
                        <input wire:model.live="direccion" type="text" id="direccion" maxlength="255" class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white transition-colors" placeholder="Ej: Calle 123 # 45-67">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-xs text-gray-400">{{ strlen($direccion) }}/255</span>
                        </div>
                    </div>
                    @error('direccion') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Correo -->
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
                    <input wire:model.live="correo" type="email" id="correo" class="block w-full px-4 py-3 mt-1 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white transition-colors" placeholder="Ej: contacto@parking.com">
                    @error('correo') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                    <input wire:model.live="telefono" type="text" id="telefono" class="block w-full px-4 py-3 mt-1 border border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white transition-colors" placeholder="Ej: +57 300 1234567">
                    @error('telefono') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="inline-flex justify-center px-6 py-3 text-sm font-medium text-white transition bg-indigo-600 border border-transparent rounded-xl shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="actualizar">Guardar Cambios</span>
                    <span wire:loading wire:target="actualizar">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Guardando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

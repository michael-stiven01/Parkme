<div class="py-6 flex justify-center items-center w-full h-full">
    <div class="w-full max-w-2xl bg-slate-800/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 transform transition-all border border-slate-700/50 mt-10 mb-10">
        
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold tracking-tight text-white mb-2">¡Bienvenido a Parkme<span class="text-emerald-500">!</span></h2>
            <p class="text-slate-400 text-sm">Antes de continuar al dashboard, cuéntanos sobre tu parqueadero.</p>
        </div>

        @if (session()->has('error'))
            <div class="mb-6 p-3 text-sm text-red-500 bg-red-500/10 rounded-xl border border-red-500/20 text-center">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="registrar" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Nombre -->
                <div class="col-span-1 md:col-span-2">
                    <label for="nombre" class="block mb-1 text-sm font-medium text-slate-300">Nombre de la Empresa / Parqueadero</label>
                    <div class="relative">
                        <input wire:model.live="nombre" type="text" id="nombre" maxlength="150" class="bg-slate-900 border @error('nombre') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="Ej: Parking Center">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-xs text-slate-500">{{ strlen($nombre) }}/150</span>
                        </div>
                    </div>
                    @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo -->
                <div>
                    <label for="tipo" class="block mb-1 text-sm font-medium text-slate-300">Tipo de Parqueadero</label>
                    <select wire:model.live="tipo" id="tipo" class="bg-slate-900 border @error('tipo') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors">
                        <option value="">Selecciona un tipo...</option>
                        <option value="Público">Público</option>
                        <option value="Privado">Privado</option>
                        <option value="Comercial">Comercial</option>
                        <option value="Residencial">Residencial</option>
                        <option value="Mixto">Mixto</option>
                    </select>
                    @error('tipo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- NIT -->
                <div>
                    <label for="nit" class="block mb-1 text-sm font-medium text-slate-300">NIT</label>
                    <input wire:model.live="nit" type="text" id="nit" class="bg-slate-900 border @error('nit') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="Ej: 900123456-7">
                    @error('nit') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Dirección -->
                <div class="col-span-1 md:col-span-2">
                    <label for="direccion" class="block mb-1 text-sm font-medium text-slate-300">Dirección</label>
                    <div class="relative">
                        <input wire:model.live="direccion" type="text" id="direccion" maxlength="255" class="bg-slate-900 border @error('direccion') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="Ej: Calle 123 # 45-67">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-xs text-slate-500">{{ strlen($direccion) }}/255</span>
                        </div>
                    </div>
                    @error('direccion') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Correo -->
                <div>
                    <label for="correo" class="block mb-1 text-sm font-medium text-slate-300">Correo Electrónico</label>
                    <input wire:model.live="correo" type="email" id="correo" class="bg-slate-900 border @error('correo') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="Ej: contacto@parking.com">
                    @error('correo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block mb-1 text-sm font-medium text-slate-300">Teléfono</label>
                    <input wire:model.live="telefono" type="text" id="telefono" class="bg-slate-900 border @error('telefono') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="Ej: +57 300 1234567">
                    @error('telefono') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" class="block w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 focus:ring-4 focus:outline-none focus:ring-emerald-500/50 text-white font-semibold rounded-xl text-center transition-all duration-200 shadow-lg shadow-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="registrar">Completar Registro</span>
                    <span wire:loading wire:target="registrar">
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

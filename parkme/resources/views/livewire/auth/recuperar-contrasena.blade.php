<div class="py-6 flex justify-center items-center h-full">
    <div class="w-full max-w-sm bg-slate-800/80 backdrop-blur-sm rounded-3xl shadow-2xl p-7 transform transition-all border border-slate-700/50">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-white mb-2">Restaurar Acceso</h1>
            <p class="text-slate-400 text-sm">Ingresa tu correo para recibir un enlace seguro de recuperación.</p>
        </div>

        @if ($errors->has('general'))
            <div class="mb-4 p-3 text-sm text-red-500 bg-red-500/10 rounded-xl border border-red-500/20">
                {{ $errors->first('general') }}
            </div>
        @endif

        <form wire:submit.prevent="solicitar" class="space-y-4">
            
            <!-- Correo -->
            <div>
                <label for="correo" class="block mb-1 text-sm font-medium text-slate-300">
                    Correo electrónico asociado
                </label>
                <div class="relative">
                    <input type="email" id="correo" wire:model.live="correo" maxlength="150"
                        class="bg-slate-900 border @error('correo') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors"
                        placeholder="john@example.com"
                        autofocus>
                </div>
                @error('correo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4">
                <button type="submit" 
                        class="block w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 focus:ring-4 focus:outline-none focus:ring-emerald-500/50 text-white font-semibold rounded-xl text-center transition-all duration-200 shadow-lg shadow-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="solicitar">Enviar Código</span>
                    <span wire:loading wire:target="solicitar">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Comprobando...
                    </span>
                </button>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-sm text-slate-400">
                    <a href="{{ route('login') }}" class="text-emerald-500 hover:underline inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-1">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver al inicio
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>

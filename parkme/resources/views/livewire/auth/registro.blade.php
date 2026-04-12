<div class="py-6 flex justify-center items-center h-full">
    <div class="w-full max-w-md bg-slate-800/80 backdrop-blur-sm rounded-3xl shadow-2xl p-6 transform transition-all border border-slate-700/50">
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">Crear cuenta</h1>
            <p class="text-slate-400 text-sm">Registra tus datos para comenzar en Parkme<span class="text-blue-500">!</span></p>
        </div>

        @if (session()->has('general'))
            <div class="mb-3 p-3 text-sm text-red-500 bg-red-500/10 rounded-xl border border-red-500/20">
                {{ session('general') }}
            </div>
        @endif

        @if ($errors->has('general'))
            <div class="mb-3 p-3 text-sm text-red-500 bg-red-500/10 rounded-xl border border-red-500/20">
                {{ $errors->first('general') }}
            </div>
        @endif

        <form wire:submit.prevent="registrar" class="space-y-3">
            
            <!-- Nombres -->
            <div>
                <label for="nombres" class="block mb-1 text-sm font-medium text-slate-300">
                    Nombres <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" id="nombres" wire:model.live="nombres" maxlength="100"
                        class="bg-slate-900 border @error('nombres') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-colors"
                        placeholder="John">
                    <div class="absolute right-3 top-2.5 text-xs text-slate-500">
                        {{ strlen($nombres) }}/100
                    </div>
                </div>
                @error('nombres') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Apellidos -->
            <div>
                <label for="apellidos" class="block mb-1 text-sm font-medium text-slate-300">
                    Apellidos <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" id="apellidos" wire:model.live="apellidos" maxlength="100"
                        class="bg-slate-900 border @error('apellidos') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-colors"
                        placeholder="Doe">
                    <div class="absolute right-3 top-2.5 text-xs text-slate-500">
                        {{ strlen($apellidos) }}/100
                    </div>
                </div>
                @error('apellidos') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Correo -->
            <div>
                <label for="correo" class="block mb-1 text-sm font-medium text-slate-300">
                    Correo electrónico <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="email" id="correo" wire:model.live="correo" maxlength="150"
                        class="bg-slate-900 border @error('correo') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-colors"
                        placeholder="john@example.com">
                    
                    @if(strlen($correo) > 0 && !$errors->has('correo'))
                        <div class="absolute right-3 top-2.5 text-emerald-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    @endif
                </div>
                @error('correo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Contraseña -->
            <div>
                <label for="contrasena" class="block mb-1 text-sm font-medium text-slate-300">
                    Contraseña <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="{{ $showPassword ? 'text' : 'password' }}" id="contrasena" wire:model.live="contrasena"
                        class="bg-slate-900 border @error('contrasena') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pr-10 transition-colors"
                        placeholder="••••••••">
                    <button type="button" wire:click="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-300 focus:outline-none">
                        @if ($showPassword)
                            <!-- Eye Closed -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        @else
                            <!-- Eye Open -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @endif
                    </button>
                </div>
                <!-- Password Strength logic -->
                @if(strlen($contrasena) > 0)
                    @php
                        $strength = 0;
                        if(strlen($contrasena) >= 8) $strength++;
                        if(preg_match('/[a-z]/', $contrasena) && preg_match('/[A-Z]/', $contrasena)) $strength++;
                        if(preg_match('/[0-9]/', $contrasena)) $strength++;
                        if(preg_match('/[^a-zA-Z0-9]/', $contrasena)) $strength++;
                        
                        $strengthColor = 'bg-red-500';
                        $strengthLabel = 'Débil';
                        if($strength == 2) {
                            $strengthColor = 'bg-amber-500';
                            $strengthLabel = 'Regular';
                        } elseif ($strength == 3) {
                            $strengthColor = 'bg-lime-500';
                            $strengthLabel = 'Buena';
                        } elseif ($strength == 4) {
                            $strengthColor = 'bg-emerald-500';
                            $strengthLabel = 'Fuerte';
                        }
                    @endphp
                    <div class="mt-2 flex items-center justify-between text-[10px]">
                        <div class="flex gap-1 flex-1 mr-2">
                            <div class="h-1 flex-1 rounded-full {{ $strength >= 1 ? $strengthColor : 'bg-slate-700' }}"></div>
                            <div class="h-1 flex-1 rounded-full {{ $strength >= 2 ? $strengthColor : 'bg-slate-700' }}"></div>
                            <div class="h-1 flex-1 rounded-full {{ $strength >= 3 ? $strengthColor : 'bg-slate-700' }}"></div>
                            <div class="h-1 flex-1 rounded-full {{ $strength >= 4 ? $strengthColor : 'bg-slate-700' }}"></div>
                        </div>
                        <span class="text-slate-400 font-medium">{{ $strengthLabel }}</span>
                    </div>
                @endif
                @error('contrasena') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Confirmar Contraseña -->
            <div>
                <label for="contrasena_confirmation" class="block mb-1 text-sm font-medium text-slate-300">
                    Confirmar <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="{{ $showPassword ? 'text' : 'password' }}" id="contrasena_confirmation" wire:model.live="contrasena_confirmation"
                        class="bg-slate-900 border @error('contrasena_confirmation') border-red-500 @else border-slate-700 @enderror text-white text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-colors"
                        placeholder="••••••••">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="block w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-500/50 text-white font-semibold rounded-xl text-center transition-all duration-200 shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="registrar">Crear cuenta</span>
                    <span wire:loading wire:target="registrar">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </span>
                </button>
            </div>
            
            <div class="text-center mt-3">
                <p class="text-xs text-slate-400">
                    ¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Inicia sesión</a>
                </p>
            </div>
        </form>
    </div>
</div>

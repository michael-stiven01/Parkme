<div class="py-6 flex justify-center items-center px-4 h-full">
    <div class="w-full max-w-sm bg-slate-800/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 transform transition-all border border-slate-700/50" 
         x-data="verificationTimer()"
         x-init="initTimer()"
         @codigo-reenviado.window="resetResendTimer()">
         
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-white mb-2">Ingresa el PIN</h1>
            <p class="text-slate-400 text-sm">
                Enviamos un código de 6 dígitos a <br>
                <span class="font-semibold text-slate-300">{{ $correo }}</span>
            </p>
        </div>

        @if ($errors->has('general'))
            <div class="mb-4 p-3 text-sm text-red-500 bg-red-500/10 rounded-xl border border-red-500/20 text-center">
                {{ $errors->first('general') }}
            </div>
        @endif

        <form wire:submit.prevent="verificar" class="space-y-5">
            <div>
                <label for="codigo" class="block mb-2 text-sm font-medium text-slate-300 text-center">
                    Código de Recuperación
                </label>
                <input type="text" id="codigo" wire:model.live="codigo" maxlength="6"
                    class="bg-slate-900 border @error('codigo') border-red-500 @else border-slate-700 @enderror text-white text-3xl font-mono text-center tracking-[0.5em] rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-4 transition-colors uppercase placeholder-slate-600"
                    placeholder="------" autofocus>
                @error('codigo') <span class="text-xs text-red-500 mt-2 block text-center">{{ $message }}</span> @enderror
            </div>

            <!-- Expiration Alert -->
            <div x-show="expiresIn > 0" class="text-center text-xs text-amber-500/80">
                El código expirará en <span x-text="formatTime(expiresIn)" class="font-mono font-bold text-amber-500"></span>
            </div>
            <div x-show="expiresIn <= 0" x-cloak class="text-center text-xs text-red-500">
                El código ha expirado. Por favor, solicita uno nuevo.
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="block w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 focus:ring-4 focus:outline-none focus:ring-emerald-500/50 text-white font-semibold rounded-xl text-center transition-all duration-200 shadow-lg shadow-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="verificar">Autorizar Cambio</span>
                    <span wire:loading wire:target="verificar">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </span>
                </button>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" 
                        @click="resendTimer <= 0 ? $wire.reenviarCodigo() : null" 
                        :class="{'text-emerald-500 hover:text-emerald-400': resendTimer <= 0, 'text-slate-500 cursor-not-allowed': resendTimer > 0}"
                        class="text-sm font-medium transition-colors"
                        :disabled="resendTimer > 0">
                    <span x-show="resendTimer <= 0">Reenviar código</span>
                    <span x-show="resendTimer > 0">Reenviar en <span x-text="formatTime(resendTimer)"></span></span>
                </button>
                
                <div x-show="isResending" x-transition class="mt-2 text-xs text-emerald-500 flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-2 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Generando nuevo código...
                </div>
            </div>
        </form>
    </div>

    <!-- Alpine Timer Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('verificationTimer', () => ({
                expiresIn: 900, // 15 mins
                resendTimer: 120, // 2 mins
                isResending: false,
                interval: null,

                initTimer() {
                    this.interval = setInterval(() => {
                        if (this.expiresIn > 0) this.expiresIn--;
                        if (this.resendTimer > 0) this.resendTimer--;
                    }, 1000);
                },

                resetResendTimer() {
                    this.isResending = false;
                    this.resendTimer = 120;
                    this.expiresIn = 900;
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                    const s = (seconds % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                }
            }));
        });
    </script>
</div>

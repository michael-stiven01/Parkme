<div x-data="verificationTimer()">
    <div class="w-full max-w-sm bg-slate-800/80 backdrop-blur-sm rounded-3xl shadow-2xl p-7 transform transition-all border border-slate-700/50 text-center">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-center items-center mb-4">
                <div class="bg-emerald-500/10 p-3.5 rounded-2xl ring-1 ring-emerald-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-emerald-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white mb-2">Revisa tu correo</h1>
            <p class="text-slate-400 text-sm">
                Hemos enviado un código de 6 dígitos a <br>
                <strong class="text-white">{{ $correo }}</strong>
            </p>
        </div>

        @if (session()->has('general') || $errors->has('general'))
            <div class="mb-4 p-3 text-sm text-red-500 bg-red-500/10 rounded-xl border border-red-500/20">
                {{ session('general') ?? $errors->first('general') }}
            </div>
        @endif

        <form wire:submit.prevent="verificar" class="space-y-4">
            
            <!-- Code Input -->
            <div>
                <input type="text" id="codigo" wire:model.lazy="codigo" maxlength="6"
                    class="bg-slate-900 border @error('codigo') border-red-500 @else border-slate-700 @enderror text-white text-center text-3xl tracking-[0.5em] font-bold rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full py-4 transition-colors uppercase"
                    placeholder="------"
                    autofocus>
                @error('codigo') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
            </div>

            <!-- Timer info -->
            <div class="text-sm mt-3">
                <span class="text-slate-400">El código expirará en: </span>
                <span class="text-emerald-500 font-bold" x-text="formattedTime">15:00</span>
            </div>

            <div class="pt-4">
                <button type="submit" 
                        class="block w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 focus:ring-4 focus:outline-none focus:ring-emerald-500/50 text-white font-semibold rounded-xl text-center transition-all duration-200 shadow-lg shadow-emerald-500/30">
                    <span wire:loading.remove wire:target="verificar">Verificar y continuar</span>
                    <span wire:loading wire:target="verificar">Verificando...</span>
                </button>
            </div>
            
            <!-- Resend action -->
            <div class="mt-4">
                <button type="button" wire:click="reenviarCodigo" x-bind:disabled="!canResend"
                    class="text-sm transition-colors duration-200"
                    x-bind:class="canResend ? 'text-blue-500 hover:underline cursor-pointer' : 'text-slate-500 cursor-not-allowed'">
                    Reenviar código <span x-show="!canResend" x-text="'en ' + resendFormattedTime"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Alpine.js logic for timers -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('verificationTimer', () => ({
                timeLeft: 15 * 60, // 15 mins for expiration
                resendTimer: 2 * 60, // 2 mins lock for resend
                interval: null,

                init() {
                    this.interval = setInterval(() => {
                        if (this.timeLeft > 0) this.timeLeft--;
                        if (this.resendTimer > 0) this.resendTimer--;
                    }, 1000);
                },
                
                get formattedTime() {
                    let m = Math.floor(this.timeLeft / 60).toString().padStart(2, '0');
                    let s = (this.timeLeft % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },
                
                get canResend() {
                    return this.resendTimer <= 0;
                },
                
                get resendFormattedTime() {
                    let m = Math.floor(this.resendTimer / 60).toString().padStart(2, '0');
                    let s = (this.resendTimer % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                }
            }))
        })
    </script>
</div>

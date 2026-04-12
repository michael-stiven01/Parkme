<div>
    <!-- Menú off-canvas para vista móvil -->
    <div x-cloak x-show="sidebarOpen" class="relative z-50 lg:hidden" aria-modal="true">
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" 
             @click="sidebarOpen = false"></div>

        <div class="fixed inset-0 flex">
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition ease-in-out duration-300 transform" 
                 x-transition:enter-start="-translate-x-full" 
                 x-transition:enter-end="translate-x-0" 
                 x-transition:leave="transition ease-in-out duration-300 transform" 
                 x-transition:leave-start="translate-x-0" 
                 x-transition:leave-end="-translate-x-full" 
                 class="relative mr-16 flex w-full max-w-xs flex-1">
                
                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                    <button type="button" class="-m-2.5 p-2.5 rounded-full hover:bg-gray-800/50 transition" @click="sidebarOpen = false">
                        <span class="sr-only">Cerrar menú sidebar</span>
                        <x-atoms.icono nombre="x-mark" tamaño="lg" class="text-white" />
                    </button>
                </div>

                <!-- Contenido del Sidebar Mobile -->
                <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-slate-900 px-6 pb-4 shadow-2xl">
                    <div class="flex h-16 shrink-0 items-center mt-4">
                        <span class="text-3xl font-extrabold text-white tracking-tight">Parkme<span class="text-indigo-500">!</span></span>
                    </div>
                    @include('livewire.layout.partials.sidebar-nav')
                </div>
            </div>
        </div>
    </div>

    <!-- Menú estático lateral para escritorio -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
        <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-slate-900 px-6 pb-4 border-r border-slate-700/50 shadow-xl">
            <div class="flex h-16 shrink-0 items-center mt-4 border-b border-slate-800/50 pb-4">
                <span class="text-3xl font-extrabold text-white tracking-tight">Parkme<span class="text-indigo-500">!</span></span>
            </div>
            @include('livewire.layout.partials.sidebar-nav')
        </div>
    </div>
</div>

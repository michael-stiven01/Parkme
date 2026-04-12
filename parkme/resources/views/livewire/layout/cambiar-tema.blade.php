<button wire:click="toggleTema" class="rounded-full p-2.5 text-gray-400 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-400 dark:hover:text-indigo-400 transition-all bg-gray-50 hover:bg-indigo-50 dark:bg-gray-800/50 dark:hover:bg-gray-700 shadow-sm border border-gray-100 dark:border-gray-700" title="Cambiar tema (claro/oscuro)">
    <span class="sr-only">Cambiar tema</span>
    
    @if($tema === 'dark')
        <x-atoms.icono nombre="sun" tamaño="sm" />
    @else
        <x-atoms.icono nombre="moon" tamaño="sm" />
    @endif
    
    <!-- Alpine para sincronizar estado nativo del DOM HTML -->
    <div x-data="{ localTema: @entangle('tema') }" x-init="$watch('localTema', val => { 
        if (val === 'dark') { document.documentElement.classList.add('dark'); } 
        else { document.documentElement.classList.remove('dark'); } 
    })" class="hidden"></div>
</button>

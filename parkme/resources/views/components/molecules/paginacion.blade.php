{{-- 
  Componente Molécula: Paginación
  Descripción: Controles para recorrer resultados paginados integrados fluidamente con la lógica de Paginator de Laravel/Livewire.
--}}
@props(['paginator'])

<div class="py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
    <div class="flex-1 flex justify-between sm:hidden">
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 cursor-not-allowed">
                Anterior
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition">
                Anterior
            </button>
        @endif

        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition">
                Siguiente
            </button>
        @else
            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 cursor-not-allowed">
                Siguiente
            </span>
        @endif
    </div>
    
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Mostrando
                <span class="font-medium">{{ $paginator->firstItem() ?? 0 }}</span>
                a
                <span class="font-medium">{{ $paginator->lastItem() ?? 0 }}</span>
                de
                <span class="font-medium">{{ $paginator->total() }}</span>
                resultados
            </p>
        </div>
        <div>
            {{ $paginator->links() }}
        </div>
    </div>
</div>

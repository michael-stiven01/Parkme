{{-- 
  Componente Molécula: Buscador
  Descripción: Input decorado con el icono de lupa para búsquedas rápidas integrado a Livewire.
--}}
@props(['placeholder' => 'Buscar...', 'wire_model' => 'buscar'])

<div class="relative rounded-md shadow-sm w-full sm:max-w-xs">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <x-atoms.icono nombre="magnifying-glass" tamaño="sm" class="text-gray-400 dark:text-gray-500 hover:text-gray-500" />
    </div>
    <input type="text" wire:model.live.debounce.300ms="{{ $wire_model }}"
        class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-600 bg-white dark:bg-gray-800 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500 sm:text-sm sm:leading-6 transition-all shadow-sm"
        placeholder="{{ $placeholder }}">
</div>

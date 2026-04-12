{{-- 
  Componente Molécula: Confirmación de Eliminar
  Descripción: Modal preconfigurado para validar operaciones críticas antes de enviarlas al servidor.
--}}
@props(['nombre_item' => 'este elemento', 'mensaje_extra' => null])

<x-molecules.modal :titulo="'Eliminar ' . $nombre_item" {{ $attributes }}>
    <div class="flex items-start">
        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
            <x-atoms.icono nombre="exclamation-triangle" class="text-red-600 dark:text-red-400" />
        </div>
        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                ¿Estás seguro que deseas eliminar permanentemente <strong>{{ $nombre_item }}</strong>? Esta acción es destructiva y no se puede deshacer.
                @if($mensaje_extra)
                    <br><br>{{ $mensaje_extra }}
                @endif
            </p>
        </div>
    </div>

    <x-slot name="pie">
        <x-atoms.boton variante="peligro" wire:loading.attr="disabled" {{ $attributes->wire('click') }} class="w-full sm:w-auto">
            Sí, eliminar
        </x-atoms.boton>
        <x-atoms.boton variante="secundario" @click="show = false" class="w-full sm:w-auto mt-3 sm:mt-0">
            Cancelar
        </x-atoms.boton>
    </x-slot>
</x-molecules.modal>

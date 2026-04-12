{{-- 
  Componente Molécula: Modal
  Descripción: Contenedor tipo diálogo para mostrar interfaces con focus exclusivo, incluye lógica de estado Livewire cruzada con Alpine.
--}}
@props(['titulo' => '', 'tamaño' => 'md'])

@php
    $sizeClasses = match($tamaño ?? 'md') {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        default => 'sm:max-w-md',
    };
@endphp

<div x-data="{ show: @entangle($attributes->wire('model')) }"
     x-show="show"
     class="fixed z-50 inset-0 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
     
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
        <!-- Backdrop oscuro de atrás -->
        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 transition-opacity backdrop-blur-sm" 
             aria-hidden="true" 
             @click="show = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Panel central (modal content) -->
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl border border-gray-200 dark:border-gray-700 transform transition-all sm:my-8 sm:align-middle w-full {{ $sizeClasses }}">
            
            @if($titulo)
                <div class="bg-gray-50 dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white" id="modal-title">
                        {{ $titulo }}
                    </h3>
                    <button type="button" @click="show = false" class="rounded-md bg-transparent text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                        <span class="sr-only">Cerrar</span>
                        <x-atoms.icono nombre="x-mark" tamaño="md" />
                    </button>
                </div>
            @endif
            
            <div class="px-4 py-5 sm:p-6 text-gray-700 dark:text-gray-300">
                {{ $slot }}
            </div>

            @if(isset($pie))
                <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row-reverse sm:space-x-2 sm:space-x-reverse border-t border-gray-200 dark:border-gray-700">
                    {{ $pie }}
                </div>
            @endif
        </div>
    </div>
</div>

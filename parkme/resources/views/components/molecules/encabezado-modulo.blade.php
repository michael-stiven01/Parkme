{{-- 
  Componente Molécula: Encabezado de Módulo
  Descripción: Renderiza el título de la vista y proporciona un espacio para botones de acción secundarios o principales del módulo completo.
--}}
@props(['titulo', 'descripcion' => null, 'icono' => null])

<div class="border-b border-gray-200 dark:border-gray-700 pb-5 sm:flex sm:items-center sm:justify-between mb-6">
    <div class="sm:w-0 sm:flex-1">
        <h1 id="module-heading" class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:tracking-tight flex items-center">
            @if($icono)
                <x-atoms.icono :nombre="$icono" tamaño="lg" class="mr-3 text-gray-400 dark:text-gray-500" />
            @endif
            {{ $titulo }}
        </h1>
        @if($descripcion)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-2xl pl-1">
                {{ $descripcion }}
            </p>
        @endif
    </div>
    @if(isset($slot) && trim($slot) !== '')
        <div class="mt-4 flex sm:mt-0 sm:ml-4 gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

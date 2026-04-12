{{-- 
  Componente Atómico: Divisor
  Descripción: Elemento decorativo o semántico utilizado para separar espacios y dividir contenido verticalmente.
--}}
@props(['texto' => null])

<div {{ $attributes->merge(['class' => 'relative py-4']) }}>
    <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t border-gray-300 dark:border-gray-700"></div>
    </div>
    @if($texto)
        <div class="relative flex justify-center">
            <span class="bg-white px-3 text-sm text-gray-500 dark:bg-gray-900 dark:text-gray-400 rounded-md">
                {{ $texto }}
            </span>
        </div>
    @endif
</div>

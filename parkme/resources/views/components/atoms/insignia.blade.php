{{-- 
  Componente Atómico: Insignia (Badge)
  Descripción: Elemento visual que denota un estado de la entidad o categorización cromática.
--}}
@props([
    'texto',
    'color' => 'gris', // verde, rojo, amarillo, azul, gris, violeta
    'tamaño' => 'md' // sm, md
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-full';
    
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
    ][$tamaño] ?? 'px-3 py-1 text-sm';

    $colorClasses = [
        'verde' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'rojo' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'amarillo' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'azul' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'gris' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        'violeta' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    ][$color] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
@endphp

<span {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $colorClasses"]) }}>
    {{ $texto ?? $slot }}
</span>

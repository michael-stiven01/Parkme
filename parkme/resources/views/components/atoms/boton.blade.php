{{-- 
  Componente Atómico: Botón
  Descripción: Un botón interactivo con diferentes variantes de estilo, tamaños y estados (carga, deshabilitado), usado para ejecutar acciones.
--}}
@props([
    'variante' => 'primario', // primario, secundario, peligro, fantasma
    'tamaño' => 'md', // sm, md, lg
    'tipo' => 'button', // button, submit, reset
    'cargando' => false,
    'disabled' => false,
    'icono_izq' => null,
    'icono_der' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
    ][$tamaño] ?? 'px-4 py-2 text-base';

    $variantClasses = [
        'primario' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600',
        'secundario' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700',
        'peligro' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:bg-red-500 dark:hover:bg-red-600',
        'fantasma' => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-500 dark:text-gray-300 dark:hover:bg-gray-800',
    ][$variante] ?? 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500';
@endphp

<button
    type="{{ $tipo }}"
    {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}
    @if($disabled || $cargando) disabled @endif
>
    @if($cargando)
        <x-atoms.cargando tamaño="sm" class="mr-2" />
    @elseif($icono_izq)
        <x-atoms.icono :nombre="$icono_izq" tamaño="sm" class="mr-2" />
    @endif

    {{ $slot }}

    @if(!$cargando && $icono_der)
        <x-atoms.icono :nombre="$icono_der" tamaño="sm" class="ml-2" />
    @endif
</button>

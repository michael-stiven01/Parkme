{{-- 
  Componente Atómico: Tarjeta
  Descripción: Contenedor estilo tarjeta para agrupar de manera estructurada otra información e interfaces del sistema principal.
--}}
@props([
    'titulo' => null,
    'padding' => 'md', // sm, md, lg
])

@php
    $padClasses = [
        'sm' => 'p-3 sm:p-4',
        'md' => 'p-4 sm:p-6',
        'lg' => 'p-6 sm:p-8',
    ][$padding] ?? 'p-4 sm:p-6';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700']) }}>
    @if(isset($encabezado) || $titulo)
        <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-4 sm:px-6">
            @if(isset($encabezado))
                {{ $encabezado }}
            @else
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    {{ $titulo }}
                </h3>
            @endif
        </div>
    @endif
    
    <div class="{{ $padClasses }}">
        {{ $slot }}
    </div>
</div>

{{-- 
  Componente Atómico: Icono
  Descripción: Contenedor semántico para invocar gráficos de Heroicons con tamaños normalizados.
--}}
@props([
    'nombre',
    'tamaño' => 'md', // sm, md, lg, xl
])

@php
    $sizeClasses = [
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-6 w-6',
        'xl' => 'h-8 w-8',
    ][$tamaño] ?? 'h-5 w-5';
@endphp

<x-dynamic-component 
    :component="'heroicon-o-' . $nombre" 
    {{ $attributes->merge(['class' => "$sizeClasses shrink-0 inline-block align-middle"]) }} 
/>

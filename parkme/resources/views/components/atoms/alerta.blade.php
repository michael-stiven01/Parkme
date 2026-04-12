{{-- 
  Componente Atómico: Alerta
  Descripción: Banner de notificación transaccional que provee retroalimentación al usuario tras una acción.
--}}
@props([
    'tipo' => 'info', // exito, error, advertencia, info
    'mensaje',
    'descartable' => false
])

@php
    $colors = [
        'exito' => 'bg-green-50 p-4 rounded-md dark:bg-green-900/20 border border-green-200 dark:border-green-800',
        'error' => 'bg-red-50 p-4 rounded-md dark:bg-red-900/20 border border-red-200 dark:border-red-800',
        'advertencia' => 'bg-yellow-50 p-4 rounded-md dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800',
        'info' => 'bg-blue-50 p-4 rounded-md dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800'
    ][$tipo] ?? 'bg-gray-50 p-4 rounded-md dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800';

    $textColors = [
        'exito' => 'text-green-800 dark:text-green-400',
        'error' => 'text-red-800 dark:text-red-400',
        'advertencia' => 'text-yellow-800 dark:text-yellow-400',
        'info' => 'text-blue-800 dark:text-blue-400'
    ][$tipo] ?? 'text-gray-800 dark:text-gray-400';

    $iconos = [
        'exito' => 'check-circle',
        'error' => 'x-circle',
        'advertencia' => 'exclamation-triangle',
        'info' => 'information-circle'
    ][$tipo] ?? 'information-circle';
@endphp

<div 
    @if($descartable) x-data="{ mostrar: true }" x-show="mostrar" x-transition.opacity @endif
    {{ $attributes->merge(['class' => $colors]) }}
    role="alert"
>
    <div class="flex items-start">
        <div class="flex-shrink-0 mt-0.5">
            <x-atoms.icono :nombre="$iconos" tamaño="md" class="{{ $textColors }}" />
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-medium {{ $textColors }}">{{ $mensaje }}</p>
        </div>
        @if($descartable)
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button 
                        @click="mostrar = false"
                        type="button" 
                        class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $textColors }} hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
                    >
                        <span class="sr-only">Descartar</span>
                        <x-atoms.icono nombre="x-mark" tamaño="sm" />
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

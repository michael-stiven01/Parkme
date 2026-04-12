{{-- 
  Componente Atómico: Selector
  Descripción: Menú desplegable para selección de opciones. Integra etiqueta y visualización de errores.
--}}
@props([
    'nombre',
    'etiqueta' => null,
    'opciones' => [], // array de ['valor' => ..., 'texto' => ...]
    'requerido' => false,
    'error' => null,
    'placeholder' => 'Seleccione...'
])

<div class="mb-4 w-full">
    @if($etiqueta)
        <x-atoms.etiqueta-campo :for="$nombre" :texto="$etiqueta" :requerido="$requerido" />
    @endif

    <select
        id="{{ $nombre }}"
        name="{{ $nombre }}"
        @if($requerido) required @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-md shadow-sm sm:text-sm transition-colors duration-200 outline-none focus:ring-2 ' .
            ($error 
                ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 dark:border-red-600 dark:text-red-400 dark:focus:ring-red-500 dark:bg-gray-800' 
                : 'border-gray-300 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:focus:border-indigo-400 dark:focus:ring-indigo-400')
        ]) }}
    >
        @if($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif
        
        @foreach($opciones as $opcion)
            <option value="{{ $opcion['valor'] }}">{{ $opcion['texto'] }}</option>
        @endforeach
    </select>

    @if($error)
        <x-atoms.mensaje-error :mensaje="$error" />
    @endif
</div>

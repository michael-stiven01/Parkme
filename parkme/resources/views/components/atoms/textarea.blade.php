{{-- 
  Componente Atómico: Área de texto
  Descripción: Campo para el ingreso de texto multilínea, con soporte de validación y límites de caracteres.
--}}
@props([
    'nombre',
    'etiqueta' => null,
    'requerido' => false,
    'filas' => 4,
    'maximo' => null,
    'contador' => false,
    'error' => null,
    'placeholder' => ''
])

@php
    $model = $attributes->wire('model')->value();
@endphp
<div class="mb-4 w-full" x-data="{
    get cuenta() {
        @if($model)
            return typeof $wire !== 'undefined' ? ($wire.get('{{ $model }}') || '').toString().length : ($refs.inputElement ? $refs.inputElement.value.length : 0);
        @else
            return this._cuenta;
        @endif
    },
    _cuenta: 0
}" x-init="$nextTick(() => { _cuenta = $refs.inputElement ? $refs.inputElement.value.length : 0 })">
    @if($etiqueta || ($contador && $maximo))
        <div class="flex justify-between items-end">
            @if($etiqueta)
                <x-atoms.etiqueta-campo :for="$nombre" :texto="$etiqueta" :requerido="$requerido" />
            @else
                <div></div>
            @endif

            @if($contador && $maximo)
                <p class="text-xs mb-1 ml-auto transition-colors duration-200"
                   :class="{
                       'text-red-500 dark:text-red-400 font-bold': cuenta >= {{ $maximo }},
                       'text-yellow-500 dark:text-yellow-400 font-medium': cuenta >= {{ ceil((int)$maximo * 0.85) }} && cuenta < {{ $maximo }},
                       'text-gray-500 dark:text-gray-400': cuenta < {{ ceil((int)$maximo * 0.85) }}
                   }">
                    <span x-text="cuenta"></span> / {{ $maximo }}
                </p>
            @endif
        </div>
    @endif

    <div class="relative shadow-sm rounded-md border-0 p-0">
        <textarea
            id="{{ $nombre }}"
            name="{{ $nombre }}"
            rows="{{ $filas }}"
            x-ref="inputElement"
            @if($maximo) maxlength="{{ $maximo }}" @endif
            @if($requerido) required @endif
            placeholder="{{ $placeholder }}"
            @if($contador && $maximo && !$model)
                x-on:input="_cuenta = $event.target.value.length"
            @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-md sm:text-sm transition-colors duration-200 outline-none focus:ring-2 ' .
                ($error 
                    ? 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 dark:border-red-600 dark:text-red-400 dark:placeholder-red-500 dark:focus:ring-red-500 dark:bg-gray-800' 
                    : 'border-gray-300 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400')
            ]) }}
        ></textarea>
    </div>

    @if($error)
        <x-atoms.mensaje-error :mensaje="$error" />
    @endif
</div>

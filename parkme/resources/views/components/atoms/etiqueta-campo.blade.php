{{-- 
  Componente Atómico: Etiqueta de campo
  Descripción: Etiqueta asociada a una entrada de formulario para definir su nombre e indicar requerimiento.
--}}
@props([
    'texto',
    'requerido' => false,
    'for' => null
])

<label 
    @if($for) for="{{ $for }}" @endif 
    {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1']) }}
>
    {{ $texto }}
    @if($requerido)
        <span class="text-red-500 dark:text-red-400 ml-1" title="Campo requerido">*</span>
    @endif
</label>

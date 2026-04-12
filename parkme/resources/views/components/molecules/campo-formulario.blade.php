{{-- 
  Componente Molécula: Campo de Formulario
  Descripción: Combina la etiqueta, entrada y mensajes de error en una unidad cohesiva. 
  Actúa como un wrapper del componente entrada atómico.
--}}
@props([
    'nombre',
    'etiqueta' => null,
    'tipo' => 'text',
    'requerido' => false,
    'placeholder' => '',
    'error' => null,
    'maximo' => null,
    'contador' => false,
    'ayuda' => null,
])

<x-atoms.entrada
    :nombre="$nombre"
    :etiqueta="$etiqueta"
    :tipo="$tipo"
    :requerido="$requerido"
    :placeholder="$placeholder"
    :error="$error"
    :maximo="$maximo"
    :contador="$contador"
    :ayuda="$ayuda"
    {{ $attributes }}
/>

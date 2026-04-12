{{-- 
  Componente Atómico: Mensaje de error
  Descripción: Componente de texto simple para estandarizar la forma gráfica de notificar problemas de validación en campos.
--}}
@props(['mensaje'])

<p {{ $attributes->merge(['class' => 'mt-1 text-sm text-red-600 dark:text-red-400 font-medium']) }}>
    {{ $mensaje }}
</p>

{{-- 
  Componente Molécula: Fila de Tabla
  Descripción: Fila estándar para las tablas de datos, con una configuración dinámica de botones de acciones mediante iconos.
--}}
@props(['acciones' => []])

<tr {{ $attributes->merge(['class' => 'hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors border-b border-gray-200 dark:border-gray-700']) }}>
    {{ $slot }}
    
    @if(!empty($acciones))
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium w-1">
            <div class="flex items-center justify-end space-x-3">
                @foreach($acciones as $accion)
                    <button wire:click="{{ $accion['accion'] }}" 
                            class="text-{{ $accion['color'] ?? 'indigo' }}-600 hover:text-{{ $accion['color'] ?? 'indigo' }}-900 dark:text-{{ $accion['color'] ?? 'indigo' }}-400 dark:hover:text-{{ $accion['color'] ?? 'indigo' }}-300 transition-colors focus:outline-none"
                            title="{{ ucfirst(str_replace('_', ' ', $accion['accion'])) }}">
                        <x-atoms.icono :nombre="$accion['icono']" tamaño="sm" />
                    </button>
                @endforeach
            </div>
        </td>
    @endif
</tr>

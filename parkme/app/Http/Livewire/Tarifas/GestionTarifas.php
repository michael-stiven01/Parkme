<?php

namespace App\Http\Livewire\Tarifas;

use Livewire\Component;
use App\Helpers\SPHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GestionTarifas extends Component
{
    public $tarifas = [];
    public $tiposVehiculo = [];
    public $busqueda = '';

    // Modal state
    public $modalVisible = false;
    public $modalConfirmacionVisible = false;
    public $modoEdicion = false;
    
    // Form fields
    public $tarifa_id;
    public $id_tipo_vehiculo = '';
    public $nombre = '';
    public $tipo_tarifa = '';
    public $valor = '';
    public $fraccion_minutos = '';
    public $hora_inicio = '';
    public $hora_fin = '';

    // Confirmation state
    public $tarifaSeleccionada;
    public $estadoObjetivo;

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'tipo_tarifa' => 'required|in:por_hora,por_fraccion,plana_dia,especial',
            'valor' => 'required|numeric|min:0|max:99999999.99',
            'id_tipo_vehiculo' => 'nullable',
            'fraccion_minutos' => 'required_if:tipo_tarifa,por_fraccion|nullable|integer|min:1',
            'hora_inicio' => 'required_if:tipo_tarifa,especial|nullable|date_format:H:i',
            'hora_fin' => 'required_if:tipo_tarifa,especial|nullable|date_format:H:i|after:hora_inicio',
        ];
    }

    public function mount()
    {
        if (strtolower(auth()->user()->ROL) !== 'administrador') {
            abort(403, 'Acceso denegado. Solo administradores pueden gestionar tarifas.');
        }

        $this->cargarDatos();
    }

    public function render()
    {
        return view('livewire.tarifas.gestion-tarifas')
            ->layout('layouts.app');
    }

    public function updatedBusqueda()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $id_empresa = auth()->user()->id_empresa;

        $resultadoTarifas = SPHelper::ejecutar('sp_listar_tarifas', [
            $id_empresa,
            $this->busqueda ?: null
        ]);
        $this->tarifas = $resultadoTarifas ?? [];

        $resultadoTipos = SPHelper::ejecutar('sp_listar_tipos_vehiculo');
        $this->tiposVehiculo = $resultadoTipos ?? [];
    }

    public function abrirModalCrear()
    {
        $this->resetearFormulario();
        $this->modoEdicion = false;
        $this->modalVisible = true;
    }

    public function abrirModalEditar($id)
    {
        $this->resetValidation();
        $this->modoEdicion = true;
        
        $tarifa = collect($this->tarifas)->firstWhere('id_tarifa', $id);
        
        if ($tarifa) {
            $this->tarifa_id = $tarifa->id_tarifa;
            $this->id_tipo_vehiculo = $tarifa->id_tipo_vehiculo ?? '';
            $this->nombre = $tarifa->NOMBRE;
            $this->tipo_tarifa = $tarifa->TIPO_TARIFA;
            // Quitamos decimales si son .00 para la vista, o lo dejamos como número
            $this->valor = (float) $tarifa->VALOR;
            $this->fraccion_minutos = $tarifa->FRACCION_MINUTOS ?? '';
            $this->hora_inicio = $tarifa->HORA_INICIO ? substr($tarifa->HORA_INICIO, 0, 5) : '';
            $this->hora_fin = $tarifa->HORA_FIN ? substr($tarifa->HORA_FIN, 0, 5) : '';
            
            $this->modalVisible = true;
        }
    }

    public function cerrarModal()
    {
        $this->modalVisible = false;
        $this->modalConfirmacionVisible = false;
        $this->resetearFormulario();
    }

    public function resetearFormulario()
    {
        $this->resetValidation();
        $this->tarifa_id = null;
        $this->id_tipo_vehiculo = '';
        $this->nombre = '';
        $this->tipo_tarifa = '';
        $this->valor = '';
        $this->fraccion_minutos = '';
        $this->hora_inicio = '';
        $this->hora_fin = '';
        $this->tarifaSeleccionada = null;
        $this->estadoObjetivo = null;
    }

    public function guardar()
    {
        // En "especial", validamos la hora_fin si requiere. Si puede ser de un dia pa otro, no validamos after.
        // Quitaremos la regla after:hora_inicio si es especial que cruza la noche
        if ($this->tipo_tarifa === 'especial') {
            $this->validate([
                'nombre' => 'required|string|max:100',
                'tipo_tarifa' => 'required|in:por_hora,por_fraccion,plana_dia,especial',
                'valor' => 'required|numeric|min:0|max:99999999.99',
                'id_tipo_vehiculo' => 'nullable',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i',
            ]);
        } else {
            $this->validate();
        }

        $id_empresa = auth()->user()->id_empresa;
        $id_tv = $this->id_tipo_vehiculo !== '' ? $this->id_tipo_vehiculo : null;
        
        $fraccion = ($this->tipo_tarifa === 'por_fraccion') ? $this->fraccion_minutos : null;
        $h_inicio = ($this->tipo_tarifa === 'especial') ? $this->hora_inicio : null;
        $h_fin = ($this->tipo_tarifa === 'especial') ? $this->hora_fin : null;

        if ($this->modoEdicion) {
            SPHelper::ejecutar('sp_actualizar_tarifa', [
                $this->tarifa_id,
                $id_tv,
                $this->nombre,
                $this->tipo_tarifa,
                $this->valor,
                $fraccion,
                $h_inicio,
                $h_fin
            ]);
            session()->flash('success', 'Tarifa actualizada correctamente.');
        } else {
            SPHelper::ejecutar('sp_crear_tarifa', [
                $id_empresa,
                $id_tv,
                $this->nombre,
                $this->tipo_tarifa,
                $this->valor,
                $fraccion,
                $h_inicio,
                $h_fin
            ]);
            session()->flash('success', 'Tarifa creada correctamente.');
        }

        $this->cerrarModal();
        $this->cargarDatos();
    }

    public function confirmarCambioEstado($id, $estadoActual)
    {
        $this->tarifaSeleccionada = collect($this->tarifas)->firstWhere('id_tarifa', $id);
        $this->estadoObjetivo = $estadoActual == 1 ? 0 : 1;
        $this->modalConfirmacionVisible = true;
    }

    public function cambiarEstado()
    {
        if ($this->tarifaSeleccionada) {
            SPHelper::ejecutar('sp_cambiar_estado_tarifa', [
                $this->tarifaSeleccionada->id_tarifa,
                $this->estadoObjetivo
            ]);
            
            session()->flash('success', 'Estado de la tarifa actualizado.');
            $this->cerrarModal();
            $this->cargarDatos();
        }
    }
}

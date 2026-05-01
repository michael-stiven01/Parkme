<?php

namespace App\Http\Livewire\Vehiculos;

use App\Helpers\SPHelper;
use Livewire\Component;
use App\Services\BitacoraService;
use Exception;

class GestionVehiculos extends Component
{
    public $vehiculos = [];
    public $busqueda = '';

    // Modal properties
    public $modalVisible = false;
    public $modoEdicion = false;
    
    // Form fields
    public $id_vehiculo;
    public $id_cliente;
    public $nombre_cliente = ''; // For display in form
    public $id_tipo_vehiculo;
    public $placa;
    public $marca;
    public $descripcion;

    // Client search properties
    public $modalClientesVisible = false;
    public $busquedaCliente = '';
    public $clientes = [];

    // Master data
    public $tiposVehiculo = [];

    // Status toggle properties
    public $modalConfirmacionVisible = false;
    public $vehiculoSeleccionado;
    public $estadoObjetivo;

    protected $listeners = ['cerrarModal'];

    public function mount()
    {
        $user = auth()->user();
        
        if (!in_array(strtolower($user->ROL), ['administrador', 'recepcionista', 'portero'])) {
            abort(403, 'Acceso denegado al módulo de vehículos.');
        }

        $this->cargarTiposVehiculo();
        $this->cargarVehiculos();
    }

    public function cargarTiposVehiculo()
    {
        try {
            $resultados = SPHelper::ejecutar('sp_listar_tipos_vehiculo', []);
            $this->tiposVehiculo = $resultados ?? [];
        } catch (Exception $e) {
            session()->flash('error', 'Error al cargar tipos de vehículos: ' . $e->getMessage());
        }
    }

    public function cargarVehiculos()
    {
        $user = auth()->user();
        
        $params = [
            $user->id_empresa,
            $this->busqueda ?: null
        ];

        try {
            $resultados = SPHelper::ejecutar('sp_listar_vehiculos', $params);
            $this->vehiculos = $resultados ?? [];
        } catch (Exception $e) {
            session()->flash('error', 'Error al cargar vehículos: ' . $e->getMessage());
        }
    }

    public function updatedBusqueda()
    {
        $this->cargarVehiculos();
    }

    public function updatedBusquedaCliente()
    {
        $this->buscarClientes();
    }

    public function abrirModalCrear()
    {
        if (strtolower(auth()->user()->ROL) === 'portero') return;
        
        $this->resetForm();
        $this->modoEdicion = false;
        $this->modalVisible = true;
    }

    public function abrirModalEditar($idVehiculo)
    {
        if (strtolower(auth()->user()->ROL) === 'portero') return;

        $this->resetForm();
        $this->modoEdicion = true;
        
        try {
            $resultados = SPHelper::ejecutar('sp_obtener_vehiculo', [$idVehiculo]);
            
            if (!empty($resultados)) {
                $vehiculo = $resultados[0];

                $this->id_vehiculo = $vehiculo->id_vehiculo;
                $this->id_cliente = $vehiculo->id_cliente;
                $this->nombre_cliente = $vehiculo->NOMBRE_CLIENTE;
                $this->id_tipo_vehiculo = $vehiculo->id_tipo_vehiculo;
                $this->placa = $vehiculo->PLACA;
                $this->marca = $vehiculo->MARCA;
                $this->descripcion = $vehiculo->DESCRIPCION;
                
                $this->modalVisible = true;
            }
        } catch (Exception $e) {
            session()->flash('error', 'Error al cargar los datos del vehículo: ' . $e->getMessage());
        }
    }

    // Modal de Clientes
    public function abrirModalClientes()
    {
        if ($this->modoEdicion) return; // Client can't be changed in edit mode
        
        $this->busquedaCliente = '';
        $this->clientes = [];
        $this->modalClientesVisible = true;
    }

    public function cerrarModalClientes()
    {
        $this->modalClientesVisible = false;
    }

    public function buscarClientes()
    {
        if (strlen($this->busquedaCliente) < 2 && empty($this->busquedaCliente)) {
            $this->clientes = [];
            return;
        }

        try {
            $user = auth()->user();
            $resultados = SPHelper::ejecutar('sp_listar_clientes', [
                $user->id_empresa,
                $this->busquedaCliente ?: null
            ]);
            $this->clientes = $resultados ?? [];
        } catch (Exception $e) {
            session()->flash('error', 'Error al buscar clientes.');
        }
    }

    public function seleccionarCliente($idCliente, $nombre, $correo)
    {
        $this->id_cliente = $idCliente;
        $this->nombre_cliente = $nombre . ' (' . $correo . ')';
        $this->cerrarModalClientes();
    }

    public function cerrarModal()
    {
        $this->modalVisible = false;
        $this->modalConfirmacionVisible = false;
        $this->modalClientesVisible = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['id_vehiculo', 'id_cliente', 'nombre_cliente', 'id_tipo_vehiculo', 'placa', 'marca', 'descripcion']);
        $this->resetValidation();
    }

    // Force uppercase and remove non-alphanumeric
    public function updatedPlaca($value)
    {
        $this->placa = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }

    public function guardar()
    {
        if (strtolower(auth()->user()->ROL) === 'portero') return;

        // Force cleanup before validation
        $this->placa = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->placa));

        $rules = [
            'descripcion' => 'nullable|string|max:255',
        ];

        if (!$this->modoEdicion) {
            $rules['id_cliente'] = 'required|integer';
            $rules['placa'] = 'required|string|max:10|min:3';
            $rules['id_tipo_vehiculo'] = 'required|integer';
            $rules['marca'] = 'nullable|string|max:80';
        }

        $this->validate($rules, [
            'id_tipo_vehiculo.required' => 'El tipo de vehículo es obligatorio.',
            'id_cliente.required' => 'Debe seleccionar un cliente.',
            'placa.required' => 'La placa es obligatoria.',
            'placa.max' => 'La placa no puede exceder los 10 caracteres.',
            'placa.min' => 'La placa debe tener al menos 3 caracteres.',
            'marca.max' => 'La marca no puede exceder los 80 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
        ]);

        try {
            $user = auth()->user();

            if (!$this->modoEdicion) {
                $existe = SPHelper::ejecutar('sp_buscar_vehiculo_por_placa', [$user->id_empresa, $this->placa]);
                if (!empty($existe)) {
                    $this->addError('placa', 'La placa ya está registrada en esta empresa.');
                    return;
                }
            }

            if ($this->modoEdicion) {
                SPHelper::ejecutar('sp_actualizar_vehiculo', [
                    $this->id_vehiculo,
                    $this->descripcion,
                ]);

                BitacoraService::registrar(
                    'Actualizar Vehículo', 
                    'Vehículos', 
                    auth()->id(), 
                    "Vehículo actualizado: {$this->placa} (ID: {$this->id_vehiculo})"
                );

                session()->flash('success', 'Vehículo actualizado correctamente.');
            } else {
                try {
                    SPHelper::ejecutar('sp_crear_vehiculo', [
                        $user->id_empresa,
                        $this->id_cliente,
                        $this->id_tipo_vehiculo,
                        $this->placa,
                        $this->marca,
                        $this->descripcion
                    ]);

                    BitacoraService::registrar(
                        'Crear Vehículo', 
                        'Vehículos', 
                        auth()->id(), 
                        "Nuevo vehículo registrado: {$this->placa} para cliente ID: {$this->id_cliente}"
                    );

                    session()->flash('success', 'Vehículo creado correctamente.');
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), '45000') !== false) {
                        $this->addError('placa', 'La placa ya está registrada en esta empresa.');
                        return;
                    }
                    throw $e;
                }
            }

            $this->cerrarModal();
            $this->cargarVehiculos();

        } catch (Exception $e) {
            session()->flash('error', 'Error al guardar el vehículo: ' . $e->getMessage());
        }
    }

    public function confirmarCambioEstado($idVehiculo, $estadoActual)
    {
        if (strtolower(auth()->user()->ROL) === 'portero') return;

        try {
            $resultados = SPHelper::ejecutar('sp_obtener_vehiculo', [$idVehiculo]);
            
            if (!empty($resultados)) {
                $this->vehiculoSeleccionado = $resultados[0];
                $this->estadoObjetivo = $estadoActual == 1 ? 0 : 1;
                $this->modalConfirmacionVisible = true;
            }
        } catch (Exception $e) {
            session()->flash('error', 'Error al cargar los datos del vehículo: ' . $e->getMessage());
        }
    }

    public function cambiarEstado()
    {
        if (!$this->vehiculoSeleccionado || strtolower(auth()->user()->ROL) === 'portero') return;

        try {
            SPHelper::ejecutar('sp_cambiar_estado_vehiculo', [
                $this->vehiculoSeleccionado->id_vehiculo,
                $this->estadoObjetivo
            ]);

            $accion = $this->estadoObjetivo == 1 ? 'activado' : 'desactivado';
            BitacoraService::registrar(
                'Cambiar Estado Vehículo', 
                'Vehículos', 
                auth()->id(), 
                "Vehículo {$accion}: {$this->vehiculoSeleccionado->PLACA}"
            );

            session()->flash('success', "Vehículo {$accion} correctamente.");
            $this->cerrarModal();
            $this->cargarVehiculos();

        } catch (Exception $e) {
            session()->flash('error', 'Error al cambiar el estado del vehículo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.vehiculos.gestion-vehiculos')->layout('layouts.app');
    }
}

<?php

namespace App\Http\Livewire\Clientes;

use App\Helpers\SPHelper;
use Livewire\Component;
use App\Services\BitacoraService;
use Exception;

class GestionClientes extends Component
{
    public $clientes = [];
    public $busqueda = '';

    // Modal properties
    public $modalVisible = false;
    public $modoEdicion = false;
    
    // Form fields
    public $id_cliente;
    public $nombres;
    public $apellidos;
    public $correo;
    public $telefono;

    // Status toggle properties
    public $modalConfirmacionVisible = false;
    public $clienteSeleccionado;
    public $estadoObjetivo;

    protected $listeners = ['cerrarModal'];

    public function mount()
    {
        $user = auth()->user();
        
        if (!in_array(strtolower($user->ROL), ['administrador', 'recepcionista', 'portero'])) {
            abort(403, 'Acceso denegado al módulo de clientes.');
        }

        $this->cargarClientes();
    }

    public function cargarClientes()
    {
        $user = auth()->user();
        
        $params = [
            $user->id_empresa,
            $this->busqueda ?: null
        ];

        $resultados = SPHelper::ejecutar('sp_listar_clientes', $params);
        $this->clientes = $resultados ?? [];
    }

    public function updatedBusqueda()
    {
        $this->cargarClientes();
    }

    public function abrirModalCrear()
    {
        if (strtolower(auth()->user()->ROL) === 'portero') return;
        
        $this->resetForm();
        $this->modoEdicion = false;
        $this->modalVisible = true;
    }

    public function abrirModalEditar($idCliente)
    {
        if (strtolower(auth()->user()->ROL) === 'portero') return;

        $this->resetForm();
        $this->modoEdicion = true;
        
        try {
            $resultados = SPHelper::ejecutar('sp_obtener_cliente', [$idCliente]);
            
            if (!empty($resultados)) {
                $cliente = $resultados[0];

                $this->id_cliente = $cliente->id_cliente;
                $this->nombres = $cliente->NOMBRES;
                $this->apellidos = $cliente->APELLIDOS;
                $this->correo = $cliente->CORREO;
                $this->telefono = $cliente->TELEFONO;
                
                $this->modalVisible = true;
            }
        } catch (Exception $e) {
            session()->flash('error', 'Error al cargar los datos del cliente: ' . $e->getMessage());
        }
    }

    public function cerrarModal()
    {
        $this->modalVisible = false;
        $this->modalConfirmacionVisible = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['id_cliente', 'nombres', 'apellidos', 'correo', 'telefono']);
        $this->resetValidation();
    }

    public function guardar()
    {
        if (strtolower(auth()->user()->ROL) === 'portero') return;

        $rules = [
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'required|email|max:150',
            'telefono' => 'nullable|string|max:20|regex:/^[\d\s\+]+$/',
        ];

        $this->validate($rules, [
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden exceder los 100 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.max' => 'Los apellidos no pueden exceder los 100 caracteres.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'correo.max' => 'El correo no puede exceder los 150 caracteres.',
            'telefono.max' => 'El teléfono no puede exceder los 20 caracteres.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios y el signo +.',
        ]);

        try {
            $user = auth()->user();

            $existente = SPHelper::ejecutar('sp_buscar_cliente_por_correo', [$user->id_empresa, $this->correo]);
            
            if (!empty($existente)) {
                $clienteExistente = $existente[0];
                if (!$this->modoEdicion || ($this->modoEdicion && $clienteExistente->id_cliente != $this->id_cliente)) {
                    $this->addError('correo', 'Este correo ya está registrado para otro cliente en la empresa.');
                    return;
                }
            }

            if ($this->modoEdicion) {
                SPHelper::ejecutar('sp_actualizar_cliente', [
                    $this->id_cliente,
                    $this->nombres,
                    $this->apellidos,
                    $this->correo,
                    $this->telefono,
                ]);

                BitacoraService::registrar(
                    'Actualizar Cliente', 
                    'Clientes', 
                    auth()->id(), 
                    "Cliente actualizado: {$this->nombres} {$this->apellidos} (ID: {$this->id_cliente})"
                );

                session()->flash('success', 'Cliente actualizado correctamente.');
            } else {
                $resultado = SPHelper::ejecutar('sp_crear_cliente', [
                    $user->id_empresa,
                    $this->nombres,
                    $this->apellidos,
                    $this->correo,
                    $this->telefono
                ]);

                $nuevoId = $resultado[0]->id_cliente ?? null;

                if ($nuevoId) {
                    BitacoraService::registrar(
                        'Crear Cliente', 
                        'Clientes', 
                        auth()->id(), 
                        "Nuevo cliente creado: {$this->nombres} {$this->apellidos} (ID: {$nuevoId})"
                    );

                    session()->flash('success', 'Cliente creado correctamente.');
                }
            }

            $this->cerrarModal();
            $this->cargarClientes();

        } catch (Exception $e) {
            session()->flash('error', 'Error al guardar el cliente: ' . $e->getMessage());
        }
    }

    public function confirmarCambioEstado($idCliente, $estadoActual)
    {
        if (strtolower(auth()->user()->ROL) === 'portero') return;

        try {
            $resultados = SPHelper::ejecutar('sp_obtener_cliente', [$idCliente]);
            
            if (!empty($resultados)) {
                $this->clienteSeleccionado = $resultados[0];
                $this->estadoObjetivo = $estadoActual == 1 ? 0 : 1;
                $this->modalConfirmacionVisible = true;
            }
        } catch (Exception $e) {
            session()->flash('error', 'Error al cargar los datos del cliente: ' . $e->getMessage());
        }
    }

    public function cambiarEstado()
    {
        if (!$this->clienteSeleccionado || strtolower(auth()->user()->ROL) === 'portero') return;

        try {
            SPHelper::ejecutar('sp_cambiar_estado_cliente', [
                $this->clienteSeleccionado->id_cliente,
                $this->estadoObjetivo
            ]);

            $accion = $this->estadoObjetivo == 1 ? 'activado' : 'desactivado';
            BitacoraService::registrar(
                'Cambiar Estado Cliente', 
                'Clientes', 
                auth()->id(), 
                "Cliente {$accion}: {$this->clienteSeleccionado->NOMBRES} {$this->clienteSeleccionado->APELLIDOS}"
            );

            session()->flash('success', "Cliente {$accion} correctamente.");
            $this->cerrarModal();
            $this->cargarClientes();

        } catch (Exception $e) {
            session()->flash('error', 'Error al cambiar el estado del cliente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.clientes.gestion-clientes')->layout('layouts.app');
    }
}

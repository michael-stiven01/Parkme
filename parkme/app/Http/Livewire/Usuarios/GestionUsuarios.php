<?php

namespace App\Http\Livewire\Usuarios;

use App\Helpers\SPHelper;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\CredencialesUsuario;
use App\Services\BitacoraService;
use Exception;

class GestionUsuarios extends Component
{
    public $usuarios = [];
    public $busqueda = '';

    // Modal properties
    public $modalVisible = false;
    public $modoEdicion = false;
    
    // Form fields
    public $id_usuario;
    public $nombres;
    public $apellidos;
    public $correo;
    public $rol = 'recepcionista';

    // Status toggle properties
    public $modalConfirmacionVisible = false;
    public $usuarioSeleccionado;
    public $estadoObjetivo;

    protected $listeners = ['cerrarModal'];

    public function mount()
    {
        $user = auth()->user();
        
        if (strtolower($user->ROL) !== 'administrador') {
            abort(403, 'Acceso denegado. Solo administradores pueden gestionar usuarios.');
        }

        $this->cargarUsuarios();
    }

    public function cargarUsuarios()
    {
        $user = auth()->user();
        
        $params = [
            $user->id_empresa
        ];

        $resultados = SPHelper::ejecutar('sp_listar_usuarios', $params);
        $this->usuarios = $resultados ?? [];
    }

    public function getUsuariosFiltradosProperty()
    {
        if (empty($this->busqueda)) {
            return $this->usuarios;
        }

        return array_filter($this->usuarios, function ($usuario) {
            $busquedaStr = strtolower($this->busqueda);
            $nombreCompleto = strtolower($usuario->NOMBRES . ' ' . $usuario->APELLIDOS);
            $correo = strtolower($usuario->CORREO);
            
            return str_contains($nombreCompleto, $busquedaStr) || 
                   str_contains($correo, $busquedaStr);
        });
    }

    public function abrirModalCrear()
    {
        $this->resetForm();
        $this->modoEdicion = false;
        $this->modalVisible = true;
    }

    public function abrirModalEditar($idUsuario)
    {
        $this->resetForm();
        $this->modoEdicion = true;
        
        try {
            $resultados = SPHelper::ejecutar('sp_obtener_usuario', [$idUsuario]);
            
            if (!empty($resultados)) {
                $usuario = $resultados[0];
                
                // Un administrador no puede editar su propio rol
                if ($usuario->id_usuario == auth()->id()) {
                    session()->flash('error', 'No puedes editar tus propios datos de acceso desde este módulo. Utiliza el módulo de perfil.');
                    return;
                }

                $this->id_usuario = $usuario->id_usuario;
                $this->nombres = $usuario->NOMBRES;
                $this->apellidos = $usuario->APELLIDOS;
                $this->correo = $usuario->CORREO;
                $this->rol = $usuario->ROL;
                
                $this->modalVisible = true;
            }
        } catch (Exception $e) {
            session()->flash('error', 'Error al cargar los datos del usuario: ' . $e->getMessage());
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
        $this->reset(['id_usuario', 'nombres', 'apellidos', 'correo', 'rol']);
        $this->resetValidation();
    }

    public function guardar()
    {
        $rules = [
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'rol' => 'required|in:administrador,recepcionista,portero',
        ];

        if ($this->modoEdicion) {
            $rules['correo'] = 'required|email|max:150';
        } else {
            $rules['correo'] = 'required|email|max:150|unique:usuarios,CORREO';
        }

        $this->validate($rules, [
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'correo.unique' => 'Este correo ya está registrado en el sistema.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
        ]);

        try {
            $user = auth()->user();

            if ($this->modoEdicion) {
                // Validación adicional en BD en caso de edición de email (que no pertenezca a otro)
                $existente = \App\Models\Usuario::where('CORREO', $this->correo)
                                                ->where('id_usuario', '!=', $this->id_usuario)
                                                ->first();
                if ($existente) {
                    $this->addError('correo', 'Este correo ya está en uso por otro usuario.');
                    return;
                }

                SPHelper::ejecutar('sp_actualizar_usuario', [
                    $this->id_usuario,
                    $this->nombres,
                    $this->apellidos,
                    $this->correo,
                    $this->rol,
                ]);

                BitacoraService::registrar(
                    'Actualizar Usuario', 
                    'Usuarios', 
                    auth()->id(), 
                    "Usuario actualizado: {$this->nombres} {$this->apellidos} (ID: {$this->id_usuario})"
                );

                session()->flash('success', 'Usuario actualizado correctamente.');
            } else {
                $contrasenaTemporal = Str::random(8);
                $hashedPassword = Hash::make($contrasenaTemporal);
                $codigoVerificacion = Str::upper(Str::random(6));
                $codigoExpiraEn = now()->addMinutes(15)->format('Y-m-d H:i:s');

                $resultado = SPHelper::ejecutar('sp_crear_usuario_empresa', [
                    $user->id_empresa,
                    $this->nombres,
                    $this->apellidos,
                    $this->correo,
                    $hashedPassword,
                    $this->rol,
                    $codigoVerificacion,
                    $codigoExpiraEn
                ]);

                $nuevoId = $resultado[0]->id_usuario ?? null;

                if ($nuevoId) {
                    BitacoraService::registrar(
                        'Crear Usuario', 
                        'Usuarios', 
                        auth()->id(), 
                        "Nuevo usuario creado: {$this->nombres} {$this->apellidos} con rol {$this->rol} (ID: {$nuevoId})"
                    );

                    // Enviar correo
                    Mail::to($this->correo)->send(new CredencialesUsuario(
                        $this->nombres,
                        $this->correo,
                        $contrasenaTemporal,
                        $this->rol,
                        $codigoVerificacion
                    ));

                    session()->flash('success', 'Usuario creado correctamente. Se le ha enviado un correo con sus credenciales y código de verificación.');
                }
            }

            $this->cerrarModal();
            $this->cargarUsuarios();

        } catch (Exception $e) {
            session()->flash('error', 'Error al guardar el usuario: ' . $e->getMessage());
        }
    }

    public function confirmarCambioEstado($idUsuario, $estadoActual)
    {
        if ($idUsuario == auth()->id()) {
            session()->flash('error', 'No puedes desactivar tu propio usuario.');
            return;
        }

        try {
            $resultados = SPHelper::ejecutar('sp_obtener_usuario', [$idUsuario]);
            
            if (!empty($resultados)) {
                $this->usuarioSeleccionado = $resultados[0];
                $this->estadoObjetivo = $estadoActual == 1 ? 0 : 1;
                $this->modalConfirmacionVisible = true;
            }
        } catch (Exception $e) {
            session()->flash('error', 'Error al cargar los datos del usuario: ' . $e->getMessage());
        }
    }

    public function cambiarEstado()
    {
        if (!$this->usuarioSeleccionado) return;

        try {
            SPHelper::ejecutar('sp_cambiar_estado_usuario', [
                $this->usuarioSeleccionado->id_usuario,
                $this->estadoObjetivo
            ]);

            $accion = $this->estadoObjetivo == 1 ? 'activado' : 'desactivado';
            BitacoraService::registrar(
                'Cambiar Estado Usuario', 
                'Usuarios', 
                auth()->id(), 
                "Usuario {$accion}: {$this->usuarioSeleccionado->NOMBRES} {$this->usuarioSeleccionado->APELLIDOS}"
            );

            session()->flash('success', "Usuario {$accion} correctamente.");
            $this->cerrarModal();
            $this->cargarUsuarios();

        } catch (Exception $e) {
            session()->flash('error', 'Error al cambiar el estado del usuario: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.usuarios.gestion-usuarios')->layout('layouts.app');
    }
}

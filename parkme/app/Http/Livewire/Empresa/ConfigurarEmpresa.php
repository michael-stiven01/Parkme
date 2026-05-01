<?php

namespace App\Http\Livewire\Empresa;

use Livewire\Component;
use App\Helpers\SPHelper;
use Illuminate\Support\Facades\Auth;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\Log;

class ConfigurarEmpresa extends Component
{
    public $nombre = '';
    public $tipo = '';
    public $direccion = '';
    public $nit = '';
    public $correo = '';
    public $telefono = '';

    protected $rules = [
        'nombre' => 'required|string|max:150',
        'tipo' => 'required|in:Público,Privado,Comercial,Residencial,Mixto',
        'direccion' => 'required|string|max:255',
        'correo' => 'required|email|max:150',
        'telefono' => 'required|string|max:20|regex:/^[\d\s\+]+$/',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre de la empresa es obligatorio.',
        'nombre.max' => 'El nombre no puede superar los 150 caracteres.',
        'tipo.required' => 'Debes seleccionar un tipo de parqueadero.',
        'tipo.in' => 'El tipo seleccionado no es válido.',
        'direccion.required' => 'La dirección es obligatoria.',
        'direccion.max' => 'La dirección no puede superar los 255 caracteres.',
        'correo.required' => 'El correo electrónico es obligatorio.',
        'correo.email' => 'Debe ser un correo electrónico válido.',
        'correo.max' => 'El correo no puede superar los 150 caracteres.',
        'telefono.required' => 'El teléfono es obligatorio.',
        'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',
        'telefono.regex' => 'El teléfono solo puede contener dígitos, espacios y el signo +.',
    ];

    public function mount()
    {
        $usuario = Auth::user();
        
        if (strtolower($usuario->ROL) !== 'administrador') {
            abort(403, 'Acceso denegado. Solo administradores pueden configurar la empresa.');
        }

        $resultado = SPHelper::ejecutar('sp_obtener_empresa', [$usuario->id_empresa]);

        if (!empty($resultado)) {
            $empresa = $resultado[0];
            $this->nombre = $empresa->NOMBRE;
            $this->tipo = $empresa->TIPO;
            $this->direccion = $empresa->DIRECCION;
            $this->nit = $empresa->NIT;
            $this->correo = $empresa->CORREO;
            $this->telefono = $empresa->TELEFONO;
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function actualizar()
    {
        $this->validate();

        try {
            $usuario = Auth::user();
            
            SPHelper::ejecutar('sp_actualizar_empresa', [
                $usuario->id_empresa,
                $this->nombre,
                $this->tipo,
                $this->direccion,
                $this->correo,
                $this->telefono
            ]);

            BitacoraService::registrar('Actualización de configuración', 'Empresa', $usuario->id_usuario, "Empresa actualizada (ID: {$usuario->id_empresa})");

            session()->flash('success', 'La configuración de la empresa ha sido actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error actualizando empresa: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error inesperado al actualizar la configuración.');
        }
    }

    public function render()
    {
        return view('livewire.empresa.configurar-empresa');
    }
}

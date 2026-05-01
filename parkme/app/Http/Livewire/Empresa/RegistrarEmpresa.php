<?php

namespace App\Http\Livewire\Empresa;

use Livewire\Component;
use App\Helpers\SPHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\BitacoraService;

class RegistrarEmpresa extends Component
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
        'nit' => 'required|string|max:20|regex:/^[\d\-]+$/',
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
        'nit.required' => 'El NIT es obligatorio.',
        'nit.max' => 'El NIT no puede superar los 20 caracteres.',
        'nit.regex' => 'El NIT solo puede contener dígitos y guiones.',
        'correo.required' => 'El correo electrónico es obligatorio.',
        'correo.email' => 'Debe ser un correo electrónico válido.',
        'correo.max' => 'El correo no puede superar los 150 caracteres.',
        'telefono.required' => 'El teléfono es obligatorio.',
        'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',
        'telefono.regex' => 'El teléfono solo puede contener dígitos, espacios y el signo +.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function registrar()
    {
        $this->validate();

        try {
            $resultado = SPHelper::ejecutar('sp_crear_empresa', [
                $this->nombre,
                $this->tipo,
                $this->direccion,
                $this->nit,
                $this->correo,
                $this->telefono
            ]);

            if (!empty($resultado) && isset($resultado[0]->id_empresa)) {
                $id_empresa = $resultado[0]->id_empresa;
                $usuario = Auth::user();
                
                SPHelper::ejecutar('sp_asignar_empresa_usuario', [
                    $usuario->id_usuario,
                    $id_empresa
                ]);

                BitacoraService::registrar('Registro de nueva empresa', 'Empresas', $usuario->id_usuario, "Empresa: {$this->nombre} (ID: {$id_empresa})");

                return redirect()->route('dashboard');
            } else {
                session()->flash('error', 'Ocurrió un error al registrar la empresa. Por favor, intenta de nuevo.');
            }
        } catch (\Exception $e) {
            Log::error('Error registrando empresa: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error inesperado al registrar la empresa.');
        }
    }

    public function render()
    {
        return view('livewire.empresa.registrar-empresa')->layout('layouts.auth');
    }
}

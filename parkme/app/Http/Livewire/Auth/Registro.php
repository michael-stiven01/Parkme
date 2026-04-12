<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Services\AuthService;
use Illuminate\Validation\Rules\Password;
use Exception;

class Registro extends Component
{
    public $nombres = '';
    public $apellidos = '';
    public $correo = '';
    public $contrasena = '';
    public $contrasena_confirmation = '';
    
    public $showPassword = false;

    protected function rules()
    {
        return [
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'required|email|max:150|unique:usuarios,CORREO',
            'contrasena' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    protected $messages = [
        'nombres.required' => 'Los nombres son requeridos.',
        'nombres.max' => 'Los nombres no pueden exceder los 100 caracteres.',
        'apellidos.required' => 'Los apellidos son requeridos.',
        'apellidos.max' => 'Los apellidos no pueden exceder los 100 caracteres.',
        'correo.required' => 'El correo electrónico es requerido.',
        'correo.email' => 'Debe ser un correo electrónico válido.',
        'correo.unique' => 'Este correo ya se encuentra registrado.',
        'correo.max' => 'El correo no puede exceder los 150 caracteres.',
        'contrasena.required' => 'La contraseña es requerida.',
        'contrasena.confirmed' => 'Las contraseñas no coinciden.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function registrar(AuthService $authService)
    {
        $this->validate();

        try {
            $authService->registrarUsuario([
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'correo' => $this->correo,
                'contrasena' => $this->contrasena,
            ]);

            session(['registro_correo' => $this->correo]);

            return redirect()->route('verification.notice');

        } catch (Exception $e) {
            $this->addError('general', 'No se pudo completar el registro: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.registro')->layout('layouts.auth');
    }
}

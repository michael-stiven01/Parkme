<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Helpers\SPHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class NuevaContrasena extends Component
{
    public $contrasena = '';
    public $contrasena_confirmation = '';
    public $showPassword = false;
    public $correo = '';

    protected function rules()
    {
        return [
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
        'contrasena.required' => 'La contraseña es requerida.',
        'contrasena.confirmed' => 'Las contraseñas no coinciden.',
    ];

    public function mount()
    {
        $this->correo = session('recovery_correo');

        if (!$this->correo) {
            return redirect()->route('password.request');
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function restablecer()
    {
        $this->validate();

        try {
            $hashedPassword = Hash::make($this->contrasena);

            // Save via SP
            SPHelper::ejecutar('sp_actualizar_contrasena', [
                $this->correo,
                $hashedPassword
            ]);

            // Clear recovery session state
            session()->forget('recovery_correo');
            
            // Flash success message to login
            session()->flash('success', 'Tu contraseña ha sido restaurada exitosamente. Ahora puedes iniciar sesión.');
            return redirect()->route('login');

        } catch (\Exception $e) {
            $this->addError('general', 'Error al actualizar la contraseña. Por favor intenta de nuevo.');
        }
    }

    public function render()
    {
        return view('livewire.auth.nueva-contrasena')->layout('layouts.auth');
    }
}

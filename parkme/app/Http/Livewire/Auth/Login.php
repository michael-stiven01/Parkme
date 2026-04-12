<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Helpers\SPHelper;
use App\Services\SesionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends Component
{
    public $correo = '';
    public $contrasena = '';
    public $recordar = false;
    public $showPassword = false;

    protected function rules()
    {
        return [
            'correo' => 'required|email',
            'contrasena' => 'required|string',
        ];
    }

    protected $messages = [
        'correo.required' => 'El correo electrónico es requerido.',
        'correo.email' => 'Debe ser un correo electrónico válido.',
        'contrasena.required' => 'La contraseña es requerida.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    protected function tryAuthenticate(SesionService $sesionService)
    {
        // Rate limiting key based on email and IP
        $key = 'login:' . Str::lower($this->correo) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('general', "Demasiados intentos de inicio de sesión. Por favor intente nuevamente en {$seconds} segundos.");
            return;
        }

        try {
            // Buscamos al usuario usando el Stored Procedure
            $resultado = SPHelper::ejecutar('sp_verificar_usuario', [$this->correo]);

            // Si el arreglo viene vacío, devolvemos error genérico y sumamos intento a limitador
            if (empty($resultado)) {
                RateLimiter::hit($key, 600); // 10 minutes lockout
                $this->addError('general', 'El correo o la contraseña son incorrectos.');
                return;
            }

            $usuario = (array) $resultado[0];

            // Comprobamos la contraseña contra el hash
            if (!Hash::check($this->contrasena, $usuario['CONTRASENA'])) {
                RateLimiter::hit($key, 600); // 10 minutes lockout
                $this->addError('general', 'El correo o la contraseña son incorrectos.');
                return;
            }

            // Validar si la cuenta del usuario está activa
            if (!(bool)$usuario['ACTIVO']) {
                $this->addError('general', 'Esta cuenta se encuentra inactiva o deshabilitada.');
                return;
            }

            // Rate Limiter Clear Upon Success
            RateLimiter::clear($key);

            // Verificar si el correo ya fue confirmado
            if (!(bool)$usuario['CORREO_VERIFICADO']) {
                // Generamos lógica para redirigir a `/verificar-correo` con el correo seteado en sesión
                session(['registro_correo' => $this->correo]);
                return redirect()->route('verification.notice');
            }

            // Iniciar sesión exitosamente
            $sesionService->iniciarSesion($usuario, $this->recordar);

            // Verificar si ya tiene empresa asignada
            if (empty($usuario['id_empresa'])) {
                return redirect()->route('empresa.registrar');
            }

            // Ir al Dashboard general
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            $this->addError('general', 'Error al intentar iniciar sesión. Contacte soporte.');
        }
    }

    public function login(SesionService $sesionService)
    {
        $this->validate();
        $this->tryAuthenticate($sesionService);
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.auth');
    }
}

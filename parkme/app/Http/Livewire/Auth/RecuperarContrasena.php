<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Helpers\SPHelper;
use App\Services\AuthService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class RecuperarContrasena extends Component
{
    public $correo = '';

    protected function rules()
    {
        return [
            'correo' => 'required|email|max:150',
        ];
    }

    protected $messages = [
        'correo.required' => 'El correo electrónico es requerido.',
        'correo.email' => 'Debe ser un correo electrónico válido.',
        'correo.max' => 'El correo electrónico es demasiado largo.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function solicitar(AuthService $authService)
    {
        $this->validate();

        $key = 'recovery:' . Str::lower($this->correo) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('correo', "Demasiados intentos. Por favor intente nuevamente en {$seconds} segundos.");
            return;
        }

        try {
            // Verificar existencia
            $resultado = SPHelper::ejecutar('sp_verificar_usuario', [$this->correo]);

            if (empty($resultado)) {
                RateLimiter::hit($key, 600); // Penalti por usuario inválido
                $this->addError('correo', 'No encontramos ninguna cuenta asociada a este correo.');
                return;
            }

            // User exists, setup PIN
            $codigo = Str::upper(Str::random(6));
            $expiraEn = now()->addMinutes(15)->format('Y-m-d H:i:s');

            // Save via SP
            SPHelper::ejecutar('sp_guardar_codigo_recuperacion', [
                $this->correo,
                $codigo,
                $expiraEn
            ]);

            // Dispatch mail
            $authService->enviarCodigoRecuperacion($this->correo, $codigo);

            RateLimiter::clear($key);

            // Store in session and redirect
            session(['recovery_correo' => $this->correo]);
            return redirect()->route('verificar-recuperacion');

        } catch (\Exception $e) {
            $this->addError('general', 'Error al procesar la solicitud. Contacte soporte.');
        }
    }

    public function render()
    {
        return view('livewire.auth.recuperar-contrasena')->layout('layouts.auth');
    }
}

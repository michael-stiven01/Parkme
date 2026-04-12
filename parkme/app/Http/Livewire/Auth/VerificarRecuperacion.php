<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Services\AuthService;
use App\Helpers\SPHelper;
use Illuminate\Support\Str;

class VerificarRecuperacion extends Component
{
    public $codigo = '';
    public $correo = '';

    protected function rules()
    {
        return [
            'codigo' => 'required|string|size:6',
        ];
    }

    protected $messages = [
        'codigo.required' => 'Debes ingresar el código de recuperación.',
        'codigo.size' => 'El código debe tener exactamente 6 caracteres.',
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

    public function verificar(AuthService $authService)
    {
        $this->validate();

        try {
            $authService->verificarCodigo($this->correo, $this->codigo);
            // El SP ha seteado CODIGO_VERIFICACION = NULL y ha vuelto CORREO_VERIFICADO = 1

            // We proceed directly to new password entry
            // Session 'recovery_correo' stays alive
            return redirect()->route('nueva-contrasena');

        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function reenviarCodigo(AuthService $authService)
    {
        try {
            // Generate new code
            $codigo = Str::upper(Str::random(6));
            $expiraEn = now()->addMinutes(15)->format('Y-m-d H:i:s');

            // Save via SP
            SPHelper::ejecutar('sp_guardar_codigo_recuperacion', [
                $this->correo,
                $codigo,
                $expiraEn
            ]);

            // Dispatch
            $authService->enviarCodigoRecuperacion($this->correo, $codigo);
            
            // Dispatch browser event to reset Alpine timers
            $this->dispatch('codigo-reenviado');

        } catch (\Exception $e) {
            $this->addError('general', 'No se pudo reenviar el código. Contacte soporte.');
        }
    }

    public function render()
    {
        return view('livewire.auth.verificar-recuperacion')->layout('layouts.auth');
    }
}

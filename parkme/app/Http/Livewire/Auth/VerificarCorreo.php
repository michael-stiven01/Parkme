<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Services\AuthService;
use App\Helpers\SPHelper;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Exception;

class VerificarCorreo extends Component
{
    public $codigo = '';
    public $correo = '';

    protected $rules = [
        'codigo' => 'required|string|size:6',
    ];

    protected $messages = [
        'codigo.required' => 'Debes ingresar el código.',
        'codigo.size' => 'El código debe tener exactamente 6 caracteres.',
    ];

    public function mount()
    {
        if (!session()->has('registro_correo')) {
            return redirect()->route('register');
        }
        $this->correo = session('registro_correo');
    }

    public function verificar(AuthService $authService)
    {
        $this->validate();

        try {
            // Verify code
            $authService->verificarCodigo($this->correo, $this->codigo);

            // Fetch user
            $resultado = SPHelper::ejecutar('sp_verificar_usuario', [$this->correo]);
            
            if (empty($resultado)) {
                throw new Exception("Usuario no encontrado en la base de datos.");
            }

            // Manually build/hydrate user model from SP output to use Laravel's Auth facade
            $userRecord = (array) $resultado[0];
            $usuario = new Usuario();
            // Force fill to respect hydration without guarding problems
            $usuario->forceFill($userRecord);
            // Crucial: Laravel Auth requires primary key presence and `exists` to be true.
            $usuario->exists = true;

            // Login user
            Auth::login($usuario);

            // Clear session
            session()->forget('registro_correo');

            // Redirect to phase 5 empty route (empresa registrar)
            return redirect()->route('empresa.registrar');

        } catch (Exception $e) {
            $this->addError('codigo', 'Error: ' . $e->getMessage());
        }
    }

    public function reenviarCodigo(AuthService $authService)
    {
        try {
            $nuevoCodigo = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
            $expiraEn    = now()->addMinutes(15)->format('Y-m-d H:i:s');

            SPHelper::ejecutar('sp_guardar_codigo_recuperacion', [
                $this->correo,
                $nuevoCodigo,
                $expiraEn,
            ]);

            $authService->enviarCodigoVerificacion($this->correo, $nuevoCodigo);

            session()->flash('reenvio_ok', 'Se ha enviado un nuevo código a tu correo.');
        } catch (\Exception $e) {
            $this->addError('codigo', 'No se pudo reenviar el código. Intenta nuevamente.');
        }
    }

    public function render()
    {
        return view('livewire.auth.verificar-correo')->layout('layouts.auth');
    }
}

<?php

namespace App\Services;

use App\Helpers\SPHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\CodigoVerificacion;
use App\Mail\CodigoRecuperacion;
use Exception;

class AuthService
{
    /**
     * Handle user registration logic: hash password, generate code, call SP, and send email.
     *
     * @param array $datos ['nombres', 'apellidos', 'correo', 'contrasena']
     * @return array [correo, codigo]
     * @throws Exception
     */
    public function registrarUsuario(array $datos): array
    {
        // 1. Hash password
        $hashedPassword = Hash::make($datos['contrasena']);

        // 2. Generate 6-char alphanumeric code
        $codigo = Str::upper(Str::random(6));

        // 3. Expiration time (15 minutes from now)
        $expiraEn = now()->addMinutes(15)->format('Y-m-d H:i:s');

        // 4. Call Stored Procedure
        $resultado = SPHelper::ejecutar('sp_crear_usuario', [
            $datos['nombres'],
            $datos['apellidos'],
            $datos['correo'],
            $hashedPassword,
            $codigo,
            $expiraEn
        ]);

        // Note: sp_crear_usuario does not return rows based on its definition, 
        // it just inserts or fails. If SP fails, SPHelper will throw an exception.

        // 5. Send Verification Email
        $this->enviarCodigoVerificacion($datos['correo'], $codigo);

        return [
            'correo' => $datos['correo'],
            'codigo' => $codigo
        ];
    }

    /**
     * Handles merely resending or sending the verification code.
     *
     * @param string $correo
     * @param string $codigo
     * @return void
     */
    public function enviarCodigoVerificacion(string $correo, string $codigo): void
    {
        Mail::to($correo)->send(new CodigoVerificacion($codigo));
    }

    /**
     * Despacha un correo de recuperación de contraseña con PIN generado.
     *
     * @param string $correo
     * @param string $codigo
     * @return void
     */
    public function enviarCodigoRecuperacion(string $correo, string $codigo): void
    {
        Mail::to($correo)->send(new CodigoRecuperacion($codigo));
    }

    /**
     * Checks the verification code using stored procedures.
     *
     * @param string $correo
     * @param string $codigo
     * @return bool
     * @throws Exception
     */
    public function verificarCodigo(string $correo, string $codigo): bool
    {
        $resultados = SPHelper::ejecutar('sp_verificar_codigo_email', [
            $correo,
            $codigo
        ]);

        if (empty($resultados) || !isset($resultados[0])) {
            throw new Exception("Error al verificar el código.");
        }

        $respuesta = (object) $resultados[0];

        if ((int)$respuesta->estado !== 1) {
            throw new Exception($respuesta->mensaje);
        }

        return true;
    }
}

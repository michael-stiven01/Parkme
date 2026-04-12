<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Illuminate\Support\Facades\Session;

class SesionService
{
    /**
     * Inicia la sesión del usuario manualmente hidratando el modelo.
     *
     * @param array $usuarioData
     * @param bool $recordar
     * @return void
     */
    public function iniciarSesion(array $usuarioData, bool $recordar = false): void
    {
        $usuario = new Usuario();
        $usuario->forceFill($usuarioData);
        $usuario->exists = true;

        Auth::login($usuario, $recordar);
    }

    /**
     * Cierra la sesión activa y regenera el token CSRF por seguridad.
     *
     * @return void
     */
    public function cerrarSesion(): void
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }

    /**
     * Retorna la información del usuario actualmente autenticado como un arreglo.
     *
     * @return array|null
     */
    public function usuarioActual(): ?array
    {
        if ($this->estaAutenticado()) {
            return Auth::user()->toArray();
        }
        return null;
    }

    /**
     * Verifica si existe un usuario autenticado en la sesión.
     *
     * @return bool
     */
    public function estaAutenticado(): bool
    {
        return Auth::check();
    }
}

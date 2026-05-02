<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest.custom')->group(function () {
    Route::get('/', function () {
        return view('bienvenida');
    })->name('welcome');

    Route::get('/login', \App\Http\Livewire\Auth\Login::class)->name('login');
    Route::get('/registro', \App\Http\Livewire\Auth\Registro::class)->name('register');
    Route::get('/verificar-correo', \App\Http\Livewire\Auth\VerificarCorreo::class)->name('verification.notice');
    
    // Password Recovery Routes
    Route::get('/recuperar-contrasena', \App\Http\Livewire\Auth\RecuperarContrasena::class)->name('password.request');
    Route::get('/verificar-recuperacion', \App\Http\Livewire\Auth\VerificarRecuperacion::class)->name('verificar-recuperacion');
    Route::get('/nueva-contrasena', \App\Http\Livewire\Auth\NuevaContrasena::class)->name('nueva-contrasena');
});

Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard', \App\Http\Livewire\Dashboard\PanelPrincipal::class)->name('dashboard');
    Route::get('/parqueo/ingreso', \App\Http\Livewire\Parqueo\RegistrarIngreso::class)->name('parqueo.ingreso');
    Route::get('/parqueo/salida', \App\Http\Livewire\Parqueo\RegistrarSalida::class)->name('parqueo.salida');
    Route::get('/parqueo/historial', \App\Http\Livewire\Parqueo\HistorialRegistros::class)->name('parqueo.historial');

    Route::get('/empresa/registrar', \App\Http\Livewire\Empresa\RegistrarEmpresa::class)->name('empresa.registrar');
    Route::get('/configuracion/empresa', \App\Http\Livewire\Empresa\ConfigurarEmpresa::class)->name('empresa.configurar');
    Route::get('/usuarios', \App\Http\Livewire\Usuarios\GestionUsuarios::class)->name('usuarios');
    Route::get('/clientes', \App\Http\Livewire\Clientes\GestionClientes::class)->name('clientes');
    Route::get('/vehiculos', \App\Http\Livewire\Vehiculos\GestionVehiculos::class)->name('vehiculos');
    Route::get('/cupos', \App\Http\Livewire\Cupos\GestionCupos::class)->name('cupos');
    Route::get('/tarifas', \App\Http\Livewire\Tarifas\GestionTarifas::class)->name('tarifas');
});

// Logout can be accessed to destroy the session (we POST to it)
Route::post('/cerrar-sesion', function (\App\Services\SesionService $sesionService) {
    $sesionService->cerrarSesion();
    return redirect('/');
})->name('logout')->middleware('auth.custom');

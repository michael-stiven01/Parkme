<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('bienvenida');
    })->name('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return "Dashboard (Protegido)";
    })->name('dashboard');
});

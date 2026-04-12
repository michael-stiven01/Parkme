<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_empresa',
        'NOMBRES',
        'APELLIDOS',
        'CORREO',
        'CONTRASENA',
        'ROL',
        'CORREO_VERIFICADO',
        'CODIGO_VERIFICACION',
        'CODIGO_EXPIRA_EN',
        'TEMA',
        'ACTIVO',
    ];

    protected $hidden = [
        'CONTRASENA',
    ];

    protected $casts = [
        'CORREO_VERIFICADO' => 'boolean',
        'CODIGO_EXPIRA_EN' => 'datetime',
        'ACTIVO' => 'boolean',
    ];

    public function getAuthPasswordName()
    {
        return 'CONTRASENA';
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function registrosIngreso()
    {
        return $this->hasMany(RegistroParqueo::class, 'id_usuario_ingreso', 'id_usuario');
    }

    public function registrosSalida()
    {
        return $this->hasMany(RegistroParqueo::class, 'id_usuario_salida', 'id_usuario');
    }

    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'id_usuario', 'id_usuario');
    }
}

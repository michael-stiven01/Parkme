<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';
    protected $primaryKey = 'id_empresa';

    protected $fillable = [
        'NOMBRE',
        'TIPO',
        'DIRECCION',
        'NIT',
        'CORREO',
        'TELEFONO',
        'ACTIVO',
    ];

    protected $casts = [
        'ACTIVO' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_empresa', 'id_empresa');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_empresa', 'id_empresa');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_empresa', 'id_empresa');
    }

    public function tarifas()
    {
        return $this->hasMany(Tarifa::class, 'id_empresa', 'id_empresa');
    }

    public function cupos()
    {
        return $this->hasMany(Cupo::class, 'id_empresa', 'id_empresa');
    }

    public function registrosParqueo()
    {
        return $this->hasMany(RegistroParqueo::class, 'id_empresa', 'id_empresa');
    }

    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'id_empresa', 'id_empresa');
    }
}

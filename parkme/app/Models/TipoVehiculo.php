<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVehiculo extends Model
{
    use HasFactory;

    protected $table = 'tipos_vehiculo';
    protected $primaryKey = 'id_tipo_vehiculo';
    public $timestamps = false;

    protected $fillable = [
        'NOMBRE',
        'ICONO',
        'ACTIVO',
    ];

    protected $casts = [
        'ACTIVO' => 'boolean',
    ];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }

    public function tarifas()
    {
        return $this->hasMany(Tarifa::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }

    public function cupos()
    {
        return $this->hasMany(Cupo::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }
}

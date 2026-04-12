<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupo extends Model
{
    use HasFactory;

    protected $table = 'cupos';
    protected $primaryKey = 'id_cupo';

    protected $fillable = [
        'id_empresa',
        'id_tipo_vehiculo',
        'CODIGO',
        'ESTADO',
        'FILA',
        'COLUMNA',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }

    public function registrosParqueo()
    {
        return $this->hasMany(RegistroParqueo::class, 'id_cupo', 'id_cupo');
    }
}

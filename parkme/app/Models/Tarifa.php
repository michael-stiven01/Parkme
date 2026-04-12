<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    use HasFactory;

    protected $table = 'tarifas';
    protected $primaryKey = 'id_tarifa';

    protected $fillable = [
        'id_empresa',
        'id_tipo_vehiculo',
        'NOMBRE',
        'TIPO_TARIFA',
        'VALOR',
        'FRACCION_MINUTOS',
        'HORA_INICIO',
        'HORA_FIN',
        'ACTIVO',
    ];

    protected $casts = [
        'VALOR' => 'decimal:2',
        'ACTIVO' => 'boolean',
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
        return $this->hasMany(RegistroParqueo::class, 'id_tarifa', 'id_tarifa');
    }
}

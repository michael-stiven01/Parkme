<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroParqueo extends Model
{
    use HasFactory;

    protected $table = 'registros_parqueo';
    protected $primaryKey = 'id_registro';

    protected $fillable = [
        'id_empresa',
        'id_vehiculo',
        'id_cupo',
        'id_tarifa',
        'id_usuario_ingreso',
        'id_usuario_salida',
        'FECHA_INGRESO',
        'FECHA_SALIDA',
        'TIEMPO_MINUTOS',
        'VALOR_COBRADO',
        'ESTADO',
        'TICKET_INGRESO_ENVIADO',
        'TICKET_SALIDA_ENVIADO',
    ];

    protected $casts = [
        'FECHA_INGRESO' => 'datetime',
        'FECHA_SALIDA' => 'datetime',
        'VALOR_COBRADO' => 'decimal:2',
        'TICKET_INGRESO_ENVIADO' => 'boolean',
        'TICKET_SALIDA_ENVIADO' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo', 'id_vehiculo');
    }

    public function cupo()
    {
        return $this->belongsTo(Cupo::class, 'id_cupo', 'id_cupo');
    }

    public function tarifa()
    {
        return $this->belongsTo(Tarifa::class, 'id_tarifa', 'id_tarifa');
    }

    public function usuarioIngreso()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_ingreso', 'id_usuario');
    }

    public function usuarioSalida()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_salida', 'id_usuario');
    }
}

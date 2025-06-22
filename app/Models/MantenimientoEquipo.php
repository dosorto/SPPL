<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MantenimientoEquipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mantenimiento_equipos';

    protected $fillable = [
        'tipo_tarea_id',
        'productos_id',
        'descripcion_tarea',
        'fecha_hora_programada',
        'fecha_hora_realizada',
        'estado',
        'empleado_id',
        'observaciones',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relaciones
    public function tipoTarea()
    {
        return $this->belongsTo(TipoTareaLimpiezaMantenimiento::class, 'tipo_tarea_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}

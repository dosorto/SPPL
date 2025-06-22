<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoTareaLimpiezaMantenimiento extends Model
{
    /** @use HasFactory<\Database\Factories\TipoTareaLimpiezaMantenimientoFactory> */
    use HasFactory,SoftDeletes;

    protected $table = 'tipo_tarea_limpieza_mantenimientos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function mantenimientos()
{
    return $this->hasMany(MantenimientoEquipos::class, 'tipo_tarea_id');
}
}
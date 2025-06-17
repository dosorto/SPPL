<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoEmpleado extends Model
{
    /** @use HasFactory<\Database\Factories\TipoEmpleadoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_empleados'; 

    protected $fillable = [
        'nombre_tipo',
        'descripcion',
        'created_by',
        'updated_by',
    ];

     public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }

}

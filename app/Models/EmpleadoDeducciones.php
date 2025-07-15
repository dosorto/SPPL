<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpleadoDeducciones extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empleado_deducciones';

    protected $fillable = [
        'empleado_id',
        'deduccion_id',
        'fecha_aplicacion',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function deduccion()
    {
        return $this->belongsTo(Deducciones::class, 'deduccion_id');
    }
}

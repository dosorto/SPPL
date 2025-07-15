<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleNominas extends Model
{
    /** @use HasFactory<\Database\Factories\DetalleNominasFactory> */
        use HasFactory, SoftDeletes;

    protected $table = 'detalle_nominas';

    protected $fillable = [
        'nomina_id',
        'empleado_id',
        'sueldo_bruto',
        'deducciones',
        'total_horas_extra',
        'horas_extra_monto',
        'sueldo_neto',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function nomina()
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}

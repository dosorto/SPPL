<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class EmpleadoPercepciones extends Model
{
        use HasFactory, SoftDeletes, TenantScoped; 

    protected $table = 'empleado_percepciones';


    protected $fillable = [
        'empleado_id',
        'percepcion_id',
        'empresa_id',
        'fecha_aplicacion',
        'cantidad_horas',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->fecha_aplicacion)) {
                $model->fecha_aplicacion = now();
            }
        });
    }


    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function percepcion()
    {
        return $this->belongsTo(Percepciones::class, 'percepcion_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}

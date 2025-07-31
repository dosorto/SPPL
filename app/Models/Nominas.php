<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class Nominas extends Model
{
    /** @use HasFactory<\Database\Factories\NominasFactory> */
        use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'nominas';

    protected $fillable = [
        'mes',
        'año',
        'descripcion',
        'empresa_id',
        'empleado_id',
        'sueldo_bruto',
        'deducciones',
        'percepciones',
        'sueldo_neto',
        'cerrada',
        'tipo_pago',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relación con Empleado
    public function empleado()
    {
        return $this->belongsTo(\App\Models\Empleado::class, 'empleado_id');
    }

    // Relación con Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    protected static function boot()
    {
    parent::boot();

    static::creating(function ($model) {
        $model->año = date('Y');
    });
    }

    public function detalleNominas()
    {
        return $this->hasMany(DetalleNominas::class, 'nomina_id');
    }

}
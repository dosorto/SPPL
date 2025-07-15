<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nominas extends Model
{
    /** @use HasFactory<\Database\Factories\NominasFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'nominas';

    protected $fillable = [
        'mes',
        'año',
        'estado',
        'descripcion',
        'empresa_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

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
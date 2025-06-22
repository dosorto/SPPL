<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenProducciones extends Model
{
    /** @use HasFactory<\Database\Factories\OrdenProduccionesFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'orden_producciones';

    protected $fillable = [
        'analisis_id',
        'cantidad',
        'cantidad_solicitada',
        'unidades_id',
        'estado',
        'precio',
        'precio_total',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function analisisCalidad()
    {
        return $this->belongsTo(AnalisisCalidad::class, 'analisis_id');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidades_id');
    }

    public function rendimientos()
    {
        return $this->hasMany(Rendimiento::class, 'orden_produccion_id');
    }
}


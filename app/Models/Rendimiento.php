<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rendimiento extends Model
{
    /** @use HasFactory<\Database\Factories\RendimientoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'rendimientos';

    protected $fillable = [
        'orden_produccion_id',
        'cantidad_mp',
        'precio_mp',
        'precio_otros_mp',
        'margen_ganancia',
        'created_by',
        'updated_by',
        'deleted_by',

    ];


    public function ordenProducciones()
    {
        return $this->belongsTo(OrdenProduccion::class);
    }


    public function productoProduccion()
    {
        return $this->hasOne(ProductoProduccion::class);
    }

}
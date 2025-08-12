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
        'enviado_a_inventario_at',
        'enviado_a_inventario_por',
    ];


    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }

    public function productosFinales()
    {
        return $this->hasMany(RendimientoProducto::class, 'rendimiento_id');
    }

}
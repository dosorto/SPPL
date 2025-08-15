<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class Rendimiento extends Model
{
    use HasFactory, TenantScoped , SoftDeletes;

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

    protected $casts = [
        'enviado_a_inventario_at' => 'datetime',
        'cantidad_mp' => 'decimal:2',
        'precio_mp' => 'decimal:2',
        'precio_otros_mp' => 'decimal:2',
    ];

    public function ordenProduccion()
    {
        return $this->belongsTo(\App\Models\OrdenProduccion::class, 'orden_produccion_id');
    }

    // En la migración la tabla producto_producciones tiene la columna `rendimientos_id`
    // por eso indicamos explícitamente la FK:
    public function productosFinales()
    {
        return $this->hasMany(\App\Models\ProductoProducciones::class, 'rendimientos_id');
    }
}
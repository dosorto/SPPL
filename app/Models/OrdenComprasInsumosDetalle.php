<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;
use App\Models\Productos;
use App\Models\OrdenComprasInsumos;


class OrdenComprasInsumosDetalle extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'orden_compra_insumo_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function ordenCompraInsumo()
    {
        return $this->belongsTo(OrdenComprasInsumos::class, 'orden_compra_insumo_id');
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenComprasInsumosDetalle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orden_compras_insumos_detalles';

    protected $fillable = [
        'orden_compra_insumo_id',
        'tipo_orden_compra_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'porcentaje_grasa',
        'porcentaje_proteina',
        'porcentaje_humedad',
        'anomalias',
        'detalles_anomalias',
    ];

    public function ordenComprasInsumos()
    {
        return $this->belongsTo(OrdenComprasInsumos::class, 'orden_compra_insumo_id');
    }

    public function tipoOrdenCompra()
    {
        return $this->belongsTo(TipoOrdenCompras::class, 'tipo_orden_compra_id');
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }
}
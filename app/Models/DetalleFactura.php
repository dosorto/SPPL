<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// El nombre de la clase es "detalle_factura" (minúsculas y guion bajo)
class DetalleFactura extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'detalle_factura';

    protected $fillable = [
        'factura_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'descuento_aplicado',
        'sub_total',
        'isv_aplicado',
        'costo_unitario',
        'utilidad_unitaria',
        'tipo_precio_utilizado',
        'origen_descuento',
        'nombre_producto_snapshot',
        'sku_snapshot',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Define la relación con la factura principal.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    /**
     * Define la relación con el producto del inventario.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(\App\Models\InventarioProductos::class, 'producto_id');
    }
}

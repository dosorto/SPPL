<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenCompraProductos extends Model
{
    /** @use HasFactory<\Database\Factories\OrdenCompraProductosFactory> */
    use HasFactory;

    protected $table = 'orden_compra_productos';

    protected $fillable = [
    'producto_id',          
    'orden_compra_id',      
    'cantidad',
    'precio',
    'created_by',
    'updated_by',
    'deleted_by',
];


    public function OrdenCompra()
    {
        return $this->belongsTo(OrdenCompras::class);
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }

}

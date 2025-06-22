<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenCompras extends Model
{
    /** @use HasFactory<\Database\Factories\OrdenComprasFactory> */
    use HasFactory;

    protected $fillable = [
    'tipo_orden_compra_id',  
    'proveedor_id',         
    'empresa_id',            
    'fecha_realizada',
    'created_by',
    'updated_by',
    'deleted_by',
];

    public function tipoOrdenCompra()
    {
        return $this->belongsTo(TipoOrdenCompras::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}

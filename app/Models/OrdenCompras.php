<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\TenantScoped;

class OrdenCompras extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'tipo_orden_compra_id',
        'proveedor_id',
        'empresa_id',
        'fecha_realizada',
        'estado',
        'descripcion',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_realizada' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'estado' => 'string',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenComprasDetalle::class, 'orden_compra_id', 'id');
    }

    public function tipoOrdenCompra()
    {
        return $this->belongsTo(TipoOrdenCompras::class, 'tipo_orden_compra_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'proveedor_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function getTipoOrdenNombreAttribute()
    {
        return $this->tipoOrdenCompra ? $this->tipoOrdenCompra->nombre : 'N/A';
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class OrdenComprasInsumos extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'tipo_orden_compra_id',
        'proveedor_id',
        'empresa_id',
        'fecha_realizada',
        'estado',
        'descripcion',
        'porcentaje_grasa',
        'porcentaje_proteina',
        'porcentaje_humedad',
        'anomalias',
        'detalles_anomalias',
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
        'anomalias' => 'boolean',
        'porcentaje_grasa' => 'decimal:2',
        'porcentaje_proteina' => 'decimal:2',
        'porcentaje_humedad' => 'decimal:2',
    ];

    public function detalles()
    {
        return $this->hasMany(OrdenComprasInsumosDetalle::class, 'orden_compra_insumo_id');
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
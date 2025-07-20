<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// El nombre de la clase es "Factura" (Mayúscula inicial)
class Factura extends Model
{
    use HasFactory, TenantScoped, SoftDeletes;

    protected $table = 'facturas';

    protected $fillable = [
        'cliente_id',
        'empresa_id',
        'empleado_id',
        'fecha_factura',
        'estado',
        'subtotal',
        'impuestos',
        'total',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relación con los detalles de la factura
    public function detalles()
    {
        // Apunta al nuevo modelo "DetalleFactura"
        return $this->hasMany(detalle_factura::class, 'factura_id');
    }

    // Relación con el cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación con el empleado que vendió
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}

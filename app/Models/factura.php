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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero_factura', // Añadido
        'cai_id',         // Añadido
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

    // --- Relaciones ---

    protected $casts = [
    'fecha_factura' => 'date',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class, 'factura_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    // --- RELACIONES AÑADIDAS ---

    /**
     * Una factura puede tener un CAI (o no).
     */
    public function cai()
    {
        return $this->belongsTo(Cai::class);
    }

    /**
     * Una factura pertenece a una empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}

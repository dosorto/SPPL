<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pagos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'factura_id',
        'metodo_pago_id',
        'empresa_id',
        'monto',
        'referencia',
        'fecha_pago',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_pago' => 'datetime',
    ];

    // --- Relaciones ---

    /**
     * Un pago pertenece a una única factura.
     */
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    /**
     * Un pago se realiza con un método de pago.
     */
    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }
}

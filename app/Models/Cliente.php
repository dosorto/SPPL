<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class Cliente extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'clientes';

    protected $fillable = [
        'numero_cliente',
        'rtn',
        'persona_id',
        'empresa_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
        // 'deleted_at' => 'datetime',
    ];

    /**
     * Un cliente tiene muchas facturas (historial de compras).
     */
    public function facturas()
    {
        return $this->hasMany(\App\Models\Factura::class, 'cliente_id');
    }

    /**
     * Un cliente pertenece a una persona (relación uno a uno inversa).
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Un cliente puede pertenecer opcionalmente a una empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}

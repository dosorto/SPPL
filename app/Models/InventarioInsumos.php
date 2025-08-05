<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;
use App\Models\Productos;

class InventarioInsumos extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'empresa_id',
        'producto_id',
        'cantidad',
        'precio_costo',
    ];

    protected $casts = [
        'precio_costo' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }
}
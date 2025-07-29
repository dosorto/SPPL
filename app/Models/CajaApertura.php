<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class CajaApertura extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'caja_id',
        'empresa_id',
        'user_id',
        'monto_inicial',
        'estado',
        'fecha_apertura',
        'fecha_cierre',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'monto_inicial' => 'decimal:2',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}

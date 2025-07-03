<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

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

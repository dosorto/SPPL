<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetodoPago extends Model
{
    /** @use HasFactory<\Database\Factories\MetodoPagoFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'metodos_pagos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'requiere_referencia',
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
        'requiere_referencia' => 'boolean',
    ];

    // --- Relaciones ---

    /**
     * Un método de pago puede ser utilizado en muchos pagos.
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}

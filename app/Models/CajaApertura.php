<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CajaApertura extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'monto_inicial',
        'monto_final_calculado',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    protected static function booted()
    {
        // ✅ Se ejecuta JUSTO ANTES de crear un nuevo registro
        static::creating(function ($apertura) {
            if (Auth::check()) { // Nos aseguramos de que haya un usuario logueado
                $apertura->user_id = Auth::id();
                $apertura->fecha_apertura = now();
                $apertura->estado = 'ABIERTA';
            }
        });
    }

    /**
     * Get the user that owns the caja apertura.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
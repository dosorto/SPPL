<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\TenantScoped;


class CajaApertura extends Model
{
    use HasFactory, TenantScoped;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'empresa_id',
        'monto_inicial',
        'monto_final_calculado',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
        // --- CAMPOS NUEVOS ---
        'conteo_usuario',
        'diferencias_cierre',
        'notas_cierre',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        // --- CASTS PARA JSON ---
        'conteo_usuario' => 'array',
        'diferencias_cierre' => 'array',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // ✅ Se ejecuta JUSTO ANTES de crear un nuevo registro
        static::creating(function ($apertura) {
            if (Auth::check()) { // Nos aseguramos de que haya un usuario logueado
                $apertura->user_id = Auth::id();
                $apertura->fecha_apertura = now();
                $apertura->estado = 'ABIERTA';

                if (empty($apertura->empresa_id) && Auth::user()->empresa_id) {
                    $apertura->empresa_id = Auth::user()->empresa_id;
                }
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

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
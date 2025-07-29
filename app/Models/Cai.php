<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cai extends Model
{
    use HasFactory, TenantScoped, SoftDeletes;

    protected $table = 'cais';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cai',
        'empresa_id',
        'rango_inicial',
        'rango_final',
        'numero_actual',
        'fecha_limite_emision',
        'activo',
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
        'fecha_limite_emision' => 'date',
        'activo' => 'boolean',
    ];

    // --- Relaciones ---

    /**
     * Un CAI pertenece a una empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Un CAI puede tener muchas facturas asociadas.
     */
    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    public static function obtenerCaiSeguro($empresaId): ?Cai
    {
        return self::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->whereDate('fecha_limite_emision', '>=', now())
            ->whereColumn('numero_actual', '<=', 'rango_final')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }
}

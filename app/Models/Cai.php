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

    protected $fillable = [
        'cai',
        'empresa_id',
        'establecimiento',
        'punto_emision',
        'tipo_documento',
        'rango_inicial',
        'rango_final',
        'numero_actual',
        'fecha_limite_emision',
        'activo',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_limite_emision' => 'date',
        'activo' => 'boolean',
    ];

    // --- Relaciones ---

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    // --- Métodos funcionales ---

    /**
     * Construye el número de factura en formato fiscal: 001-001-01-00000023
     */
    public function generarNumeroFactura(): string
    {
        $correlativo = str_pad($this->numero_actual + 1, 8, '0', STR_PAD_LEFT);
        return "{$this->establecimiento}-{$this->punto_emision}-{$this->tipo_documento}-{$correlativo}";
    }

    /**
     * Devuelve el CAI activo y disponible para una empresa.
     * Bloquea el registro para uso en transacción.
     */
    public static function obtenerCaiSeguro($empresaId): ?self
    {
        return self::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->whereDate('fecha_limite_emision', '>=', now())
            ->whereColumn('numero_actual', '<', 'rango_final')
            ->orderBy('fecha_limite_emision')
            ->lockForUpdate()
            ->first();
    }
}

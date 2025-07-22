<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class Cliente extends Model
{
    use HasFactory, TenantScoped, SoftDeletes;

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

    protected static function booted(): void
    {
        static::creating(function (Cliente $cliente) {
            // Generar número de cliente automáticamente antes de guardarlo.
            // 1. Obtiene el ID máximo actual y le suma 1.
            $maxId = self::max('id') + 1;
            
            // 2. Formatea el número con un prefijo y ceros a la izquierda.
            $cliente->numero_cliente = 'CLI-' . str_pad($maxId, 5, '0', STR_PAD_LEFT);

            // También puedes asignar el 'created_by' aquí si quieres,
            // aunque parece que ya lo haces con un Trait.
            if (auth()->check() && !$cliente->created_by) {
                $cliente->created_by = auth()->id();
            }
        });
        
        // Puedes añadir los hooks para updating y deleting también si es necesario
        static::updating(function(Cliente $cliente){
             if (auth()->check() && !$cliente->updated_by) {
                $cliente->updated_by = auth()->id();
            }
        });
    }
}

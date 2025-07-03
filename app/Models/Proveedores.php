<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Paises;

class Proveedores extends Model
{
    /** @use HasFactory<\Database\Factories\ProveedoresFactory> */
    use HasFactory, SoftDeletes;

     protected $table = 'proveedores';

    protected $fillable = [
    'nombre_proveedor',
    'telefono',
    'rtn',
    'direccion',
    'municipio_id',         // FK a municipios
    'persona_contacto',
    'empresa_id',           // FK a empresas
    'created_by',
    'updated_by',
    'deleted_by',
];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function eliminadoPor()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->save(); // Guardar el deleted_by antes del soft delete
            }
        });

    }
    

}

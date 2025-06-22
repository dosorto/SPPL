<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class Proveedores extends Model
{
    /** @use HasFactory<\Database\Factories\ProveedoresFactory> */
    use HasFactory;

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

    
}

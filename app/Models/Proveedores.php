<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class Proveedores extends Model
{
    /** @use HasFactory<\Database\Factories\ProveedoresFactory> */
    use HasFactory;

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    
}

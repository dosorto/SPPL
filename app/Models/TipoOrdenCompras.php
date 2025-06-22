<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoOrdenCompras extends Model
{
    /** @use HasFactory<\Database\Factories\TipoOrdenComprasFactory> */
    use HasFactory;

    protected $table = 'tipo_orden_compras';

    protected $fillable = [
    'nombre',
    'created_by',
    'updated_by',
    'deleted_by',
];
}

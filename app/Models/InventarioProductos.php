<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventarioProductos extends Model
{
    /** @use HasFactory<\Database\Factories\InventarioProductosFactory> */
    use HasFactory;

    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }
}

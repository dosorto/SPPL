<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
    /** @use HasFactory<\Database\Factories\LoteFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'lotes';

    protected $fillable = [
        'fecha_elaboracion',
        'fecha_vencimiento',
        'cantidad',
        'producto_id',
        'producto_producciones_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function productos()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function productoProducciones()
    {
        return $this->belongsTo(ProductoProducciones::class, 'producto_produccion_id');
    }
}

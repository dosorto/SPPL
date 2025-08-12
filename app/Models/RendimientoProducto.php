<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RendimientoProducto extends Model
{
    use HasFactory;

    protected $table = 'rendimiento_productos';

    protected $fillable = [
        'rendimiento_id',
        'producto_id',
        'cantidad',
        'unidad_de_medida_id',
    ];

    public function rendimiento()
    {
        return $this->belongsTo(Rendimiento::class);
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }

    public function unidadDeMedida()
    {
        return $this->belongsTo(UnidadDeMedidas::class, 'unidad_de_medida_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Muestra extends Model
{
    /** @use HasFactory<\Database\Factories\MuestraFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'muestras';

    protected $fillable = [
        'inventario_producto',
        'nombre_muestra',
        'cantidad',
        'unidades_id',
        'temperatura',
        'fecha_muestra',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function producto()
    {
        return $this->belongsTo(InventarioProducto::class, 'inventario_producto');
    }

    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidades_id');
    }

    public function analisis()
    {
        return $this->hasMany(AnalisisCalidad::class, 'muestra_id');
    }
}

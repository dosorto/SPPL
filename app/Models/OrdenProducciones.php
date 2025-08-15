<?php
/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenProduccion extends Model
{
    use SoftDeletes;

    protected $table = 'ordenes_produccion';

    protected $fillable = [
        'producto_id',
        'cantidad',
        'unidad_de_medida_id',
        'fecha_solicitud',
        'fecha_entrega',
        'estado',
        'observaciones',
        'empresa_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Asegúrate de que exista App\Models\UnidadDeMedida o ajusta el nombre de clase real
    public function unidadDeMedida()
    {
        return $this->belongsTo(\App\Models\UnidadDeMedida::class, 'unidad_de_medida_id');
    }

    // Asegúrate de que exista App\Models\Producto (singular) o ajusta a la clase correcta
    public function producto()
    {
        return $this->belongsTo(\App\Models\Producto::class, 'producto_id');
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id');
    }

    public function insumos()
    {
        return $this->hasMany(\App\Models\OrdenProduccionInsumo::class, 'orden_produccion_id');
    }

    public function rendimiento()
    {
        return $this->hasOne(\App\Models\Rendimiento::class, 'orden_produccion_id');
    }
}
    */
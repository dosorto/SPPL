<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenProduccion extends Model
{
    use SoftDeletes;

    protected $table = 'ordenes_produccion';
    protected $fillable = [
        'producto_id', 'cantidad', 'fecha_solicitud', 'fecha_entrega', 'estado', 'observaciones', 'empresa_id', 'created_by', 'updated_by', 'deleted_by'
    ];

    public function producto()
    {
        return $this->belongsTo(Productos::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function insumos()
    {
        return $this->hasMany(OrdenProduccionInsumo::class, 'orden_produccion_id');
    }
}

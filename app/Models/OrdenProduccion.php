<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class OrdenProduccion extends Model
{
    use SoftDeletes, TenantScoped;

    protected $table = 'ordenes_produccion';
    protected $fillable = [
        'producto_id', 'cantidad', 'unidad_de_medida_id', 'fecha_solicitud', 'fecha_entrega', 'estado', 'observaciones', 'empresa_id', 'created_by', 'updated_by', 'deleted_by'
    ];

    public function unidadDeMedida()
    {   
        return $this->belongsTo(\App\Models\UnidadDeMedidas::class, 'unidad_de_medida_id');
    }

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

        public function rendimiento()
        {
            return $this->hasOne(Rendimiento::class, 'orden_produccion_id');
        }
}

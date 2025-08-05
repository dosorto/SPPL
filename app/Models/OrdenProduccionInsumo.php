<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenProduccionInsumo extends Model
{
    protected $table = 'orden_produccion_insumos';
    protected $fillable = [
        'orden_produccion_id', 'insumo_id', 'cantidad_utilizada'
    ];

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Productos::class, 'insumo_id');
    }
}

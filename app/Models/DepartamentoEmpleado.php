<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Empresa;
use App\Models\Traits\TenantScoped;

class DepartamentoEmpleado extends Model
{
    /** @use HasFactory<\Database\Factories\DepartementoempleadoFactory> */
    use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'departamento_empleados'; 

    protected $fillable = [
        'nombre_departamento_empleado',
        'descripcion',
        'empresa_id',
        'created_by',
        'updated_by',
    ];
    


    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }

    public function empresa()
    {
    return $this->belongsTo(Empresa::class);
    }

    /**
     * Cambio jessuri: Esta función booted() asigna automáticamente los campos de auditoría (created_by, updated_by, deleted_by)
     * con el ID del usuario autenticado al crear, actualizar o eliminar un departamento interno.
     * Así, estos campos se llenan sin intervención manual desde el formulario.
     */
    protected static function booted()
    {
        static::creating(function ($departamentoEmpleado) {
            if (auth()->check()) {
                $departamentoEmpleado->created_by = auth()->id();
            }
        });
        static::updating(function ($departamentoEmpleado) {
            if (auth()->check()) {
                $departamentoEmpleado->updated_by = auth()->id();
            }
        });
        static::deleting(function ($departamentoEmpleado) {
            if (auth()->check()) {
                $departamentoEmpleado->deleted_by = auth()->id();
                $departamentoEmpleado->save();
            }
        });
    }
}

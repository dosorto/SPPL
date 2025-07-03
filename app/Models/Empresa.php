<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    /** @use HasFactory<\Database\Factories\EmpresaFactory> */
    use HasFactory, SoftDeletes; // Usa SoftDeletes si lo tienes en tu migración

    protected $table = 'empresas'; // Asegúrate de que el modelo esté asociado a la tabla 'empresas'

    protected $fillable = [
        'nombre',
        'pais_id', 
        'departamento_id', 
        'municipio_id',
        'direccion',
        'telefono',
        'rtn',
        'created_by',
        'updated_by',
    ];

    public function pais()
    {
        return $this->belongsTo(Paises::class);
    }
    /**
     * Define la relación inversa: una empresa pertenece a un departamento.
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
    /**
     * Define la relación inversa: una empresa pertenece a un municipio.
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function departamentosempleados()
    {
        return $this->hasMany(DepartamentoEmpleado::class);
    }

    /**
     * Una empresa puede tener muchos clientes asociados (relación inversa de cliente.empresa_id).
     */
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    /**
     * Una empresa puede tener muchos empleados asociados (relación inversa de empleado.empresa_id).
     */
    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }

    /**
     * Cambio jessuri: Esta función booted() asigna automáticamente los campos de auditoría (created_by, updated_by, deleted_by)
     * con el ID del usuario autenticado al crear, actualizar o eliminar una empresa.
     * Así, estos campos se llenan sin intervención manual desde el formulario.
     */
    protected static function booted()
    {
        static::creating(function ($empresa) {
            if (auth()->check()) {
                $empresa->created_by = auth()->id();
            }
        });
        static::updating(function ($empresa) {
            if (auth()->check()) {
                $empresa->updated_by = auth()->id();
            }
        });
        static::deleting(function ($empresa) {
            if (auth()->check()) {
                $empresa->deleted_by = auth()->id();
                $empresa->save();
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deducciones extends Model
{
    /** @use HasFactory<\Database\Factories\DeduccionesFactory> */
        use HasFactory, SoftDeletes;

    protected $table = 'deducciones';

    protected $fillable = [
        'deduccion',
        'valor',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    public function deduccionesAplicadas()
    {
        return $this->hasMany(EmpleadoDeducciones::class, 'empleado_id')->with('deduccion');
    }
}

<?php

namespace Database\Factories;

use App\Models\CategoriaProducto;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaProductoFactory extends Factory
{
    protected $model = CategoriaProducto::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->randomElement(['Producto', 'Materia Prima', 'Insumo', 'Equipo']),
            'empresa_id' => 1, // Ajustar según el tenant
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
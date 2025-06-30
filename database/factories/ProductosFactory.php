<?php

namespace Database\Factories;

use App\Models\Productos;
use App\Models\UnidadDeMedidas;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductosFactory extends Factory
{
    protected $model = Productos::class;

    public function definition()
    {
        return [
            'unidad_de_medida_id' => UnidadDeMedidas::inRandomOrder()->first()->id ?? UnidadDeMedidas::factory(),
            'nombre' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(6),
            'descripcion_corta' => $this->faker->sentence(3),
            'sku' => strtoupper($this->faker->bothify('???-#####')),
            'codigo' => $this->faker->unique()->ean8(),
            'color' => $this->faker->safeColorName(),
            'isv' => $this->faker->randomFloat(2, 0, 0.15), // ej. 0.15 = 15%
            'created_by' => 1,  // Cambia según usuarios existentes
            'updated_by' => 1,
            'deleted_by' => null,
        ];
    }
}

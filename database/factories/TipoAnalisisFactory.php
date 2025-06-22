<?php

namespace Database\Factories;

use App\Models\TipoAnalisis;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoAnalisisFactory extends Factory
{
    protected $model = TipoAnalisis::class;

    public function definition(): array
    {
        return [
            'nombre_analisis' => $this->faker->randomElement(['Grasa', 'Proteína', 'pH', 'Sólidos Totales']),
            'descripcion' => $this->faker->sentence(),
            'unidad_id' => UnidadMedida::factory(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}


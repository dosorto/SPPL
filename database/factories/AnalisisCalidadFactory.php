<?php

namespace Database\Factories;

use App\Models\AnalisisCalidad;
use App\Models\Muestra;
use App\Models\TipoAnalisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisCalidadFactory extends Factory
{
    protected $model = AnalisisCalidad::class;

    public function definition(): array
    {
        return [
            'muestra_id' => Muestra::factory(),
            'tipo_analisis_id' => TipoAnalisis::factory(),
            'valor' => $this->faker->randomFloat(2, 0.1, 5),
            'observaciones' => $this->faker->sentence(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}


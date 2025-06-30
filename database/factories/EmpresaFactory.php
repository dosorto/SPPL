<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Municipio;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->company(),
            'municipio_id' => Municipio::inRandomOrder()->first()->id ?? Municipio::factory(),
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->phoneNumber(),
            'rtn' => $this->faker->numerify('##########'),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}

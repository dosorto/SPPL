<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Municipio;
use App\Models\Empresa;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proveedores>
 */
class ProveedoresFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_proveedor' => $this->faker->company, 
            'telefono' => $this->faker->phoneNumber,    
            'rtn' => $this->faker->unique()->numerify('##########'), 
            'direccion' => $this->faker->address,      
            'municipio_id' => $this->faker->numberBetween(1,16),    
            'persona_contacto' => $this->faker->name,   
            'empresa_id' => $this->faker->numberBetween(1,10),                     
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => 1, 
            'updated_by' => 1, 
            'deleted_by' => null, 
        ];
    }
}

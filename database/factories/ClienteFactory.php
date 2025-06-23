<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cliente;
use App\Models\Persona; // Necesitamos el modelo Persona para la FK
use App\Models\Empresa; // Necesitamos el modelo Empresa para la FK (opcional)

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Genera un número de cliente único, ej: "CLI-12345"
            'num_cliente' => 'CLI-' . $this->faker->unique()->randomNumber(5),
            // Genera un RTN de 11 dígitos, que puede ser nulo
            'rtn' => $this->faker->boolean(70) ? $this->faker->unique()->numerify('###########') : null,
            
            // Asigna un 'persona_id' aleatorio de una Persona existente.
            // Esto asume que ya tienes Personas en tu base de datos.
            'persona_id' => Persona::inRandomOrder()->first()->id,

            // Asigna un 'empresa_id' aleatorio de una Empresa existente (opcionalmente).
            // Hay un 50% de probabilidad de que sea NULL.
            // Esto asume que ya tienes Empresas en tu base de datos.
            'empresa_id' => $this->faker->boolean(50) ? Empresa::inRandomOrder()->first()->id : null,

            // Campos de auditoría (logs)
            'created_by' => 1,
            'updated_by' => 1,
            'deleted_by' => null, // Por defecto, no eliminado lógicamente
        ];
    }
}


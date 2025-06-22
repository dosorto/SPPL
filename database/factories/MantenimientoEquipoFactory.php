<?php

namespace Database\Factories;

use App\Models\MantenimientoEquipo;
use App\Models\TipoTareaLimpiezaMantenimiento;
use App\Models\Producto;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class MantenimientoEquipoFactory extends Factory
{
    protected $model = MantenimientoEquipo::class;

    public function definition(): array
    {
        return [
            'tipo_tarea_id' => TipoTareaLimpiezaMantenimiento::factory(),
            'productos_id' => Producto::factory(),
            'descripcion_tarea' => $this->faker->sentence(10),
            'fecha_hora_programada' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'fecha_hora_realizada' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'estado' => $this->faker->randomElement(['PENDIENTE', 'EN_PROCESO', 'REALIZADO', 'NO_REALIZADO']),
            'empleado_id' => Empleado::factory(),
            'observaciones' => $this->faker->optional()->paragraph(),
            'created_by' => 1,
            'updated_by' => 1,
            'deleted_by' => null,
        ];
    }
}

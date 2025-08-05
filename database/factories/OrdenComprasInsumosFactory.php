<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TipoOrdenCompras;
use App\Models\Proveedores;
use App\Models\Empresa;

class OrdenComprasInsumosFactory extends Factory
{
    protected $model = \App\Models\OrdenComprasInsumos::class;

    public function definition(): array
    {
        return [
            'tipo_orden_compra_id' => TipoOrdenCompras::factory(),
            'proveedor_id' => Proveedores::factory(),
            'empresa_id' => Empresa::factory(),
            'fecha_realizada' => $this->faker->date(),
            'estado' => $this->faker->randomElement(['Pendiente', 'Recibida']),
            'descripcion' => $this->faker->paragraph,
            'porcentaje_grasa' => $this->faker->randomFloat(2, 2, 5),
            'porcentaje_proteina' => $this->faker->randomFloat(2, 2, 4),
            'porcentaje_humedad' => $this->faker->randomFloat(2, 80, 90),
            'anomalias' => $this->faker->boolean,
            'detalles_anomalias' => $this->faker->optional()->sentence,
            'created_by' => $this->faker->numberBetween(1, 10),
            'updated_by' => $this->faker->numberBetween(1, 10),
        ];
    }
}
<?php

namespace Database\Factories;

use App\Models\Productos;
use App\Models\UnidadDeMedidas;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductosFactory extends Factory
{
    protected $model = Productos::class;

    protected $productosDerivadosDeLeche = [
        'Leche Entera',
        'Leche Descremada',
        'Queso Fresco',
        'Queso Cheddar',
        'Yogur Natural',
        'Yogur Griego',
        'Mantequilla',
        'Crema de Leche',
        'Leche Condensada',
        'Leche Evaporada',
        'Requesón',
        'Queso Cottage',
        'Helado de Vainilla',
        'Leche de Cabra',
        'Queso Mozzarella',
    ];

    public function definition()
    {
        $nombreProducto = $this->faker->randomElement($this->productosDerivadosDeLeche);

        return [
            'unidad_de_medida_id' => UnidadDeMedidas::inRandomOrder()->first()->id ?? UnidadDeMedidas::factory(),
            'nombre' => $nombreProducto,
            'descripcion' => "Producto lácteo: " . $nombreProducto,
            'descripcion_corta' => $nombreProducto,
            'sku' => strtoupper($this->faker->bothify('???-#####')),
            'codigo' => $this->faker->unique()->ean8(),
            'color' => $this->faker->safeColorName(),
            'isv' => $this->faker->randomFloat(2, 0, 0.15),
            'created_by' => 1,
            'updated_by' => 1,
            'deleted_by' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Productos;
use App\Models\UnidadDeMedidas;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductosFactory extends Factory
{
    protected $model = Productos::class;

    protected $productosDerivadosDeLeche = [
        'Leche Entera', 'Leche Descremada', 'Queso Fresco', 'Queso Cheddar',
        'Yogur Natural', 'Yogur Griego', 'Mantequilla', 'Crema de Leche',
        'Leche Condensada', 'Leche Evaporada', 'Requesón', 'Queso Cottage',
        'Helado de Vainilla', 'Leche de Cabra', 'Queso Mozzarella',
    ];

    protected $materiaPrima = [
        'Leche Cruda', 'Cultivo Láctico', 'Cloruro de Calcio', 'Cuajo', 'Sal Industrial',
    ];

    protected $insumos = [
        'Envase Plástico', 'Tapa Rosca', 'Etiqueta Adhesiva', 'Bolsa Plástica', 
        'Detergente CIP', 'Desinfectante Ácido',
    ];

    protected $equiposMaquinaria = [
        'Pasteurizadora', 'Homogeneizadora', 'Envasadora', 
        'Tanque de Almacenamiento', 'Mesa Inoxidable', 'Bomba Centrífuga',
    ];

    public function definition()
    {
        $categoria = $this->faker->randomElement(['producto', 'materia_prima', 'insumo', 'equipo']);

        switch ($categoria) {
            case 'materia_prima':
                $nombreProducto = $this->faker->randomElement($this->materiaPrima);
                $descripcion = "Materia prima para producción: " . $nombreProducto;
                break;
            case 'insumo':
                $nombreProducto = $this->faker->randomElement($this->insumos);
                $descripcion = "Insumo utilizado en planta: " . $nombreProducto;
                break;
            case 'equipo':
                $nombreProducto = $this->faker->randomElement($this->equiposMaquinaria);
                $descripcion = "Equipo o maquinaria: " . $nombreProducto;
                break;
            default:
                $nombreProducto = $this->faker->randomElement($this->productosDerivadosDeLeche);
                $descripcion = "Producto lácteo: " . $nombreProducto;
                break;
        }

        return [
            'unidad_de_medida_id' => UnidadDeMedidas::inRandomOrder()->first()->id ?? UnidadDeMedidas::factory(),
            'nombre' => $nombreProducto,
            'descripcion' => $descripcion,
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
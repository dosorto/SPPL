<?php

namespace Database\Seeders;

use App\Models\CategoriaCliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definimos categorías comunes para los clientes
        $categorias = [
            [
                'nombre' => 'Premium',
                'descripcion' => 'Clientes con alto volumen de compras y larga trayectoria.',
                'activo' => true,
                'created_by' => 1,
            ],
            [
                'nombre' => 'Corporativo',
                'descripcion' => 'Empresas y clientes institucionales.',
                'activo' => true,
                'created_by' => 1,
            ],
            [
                'nombre' => 'Regular',
                'descripcion' => 'Clientes habituales con compras periódicas.',
                'activo' => true,
                'created_by' => 1,
            ],
            [
                'nombre' => 'Nuevo',
                'descripcion' => 'Clientes recién registrados o con pocas compras.',
                'activo' => true,
                'created_by' => 1,
            ],
            [
                'nombre' => 'Mayorista',
                'descripcion' => 'Clientes que compran en grandes cantidades para reventa.',
                'activo' => true,
                'created_by' => 1,
            ],
        ];

        // Insertamos las categorías en la base de datos
        foreach ($categorias as $categoria) {
            CategoriaCliente::create($categoria);
        }
    }
}

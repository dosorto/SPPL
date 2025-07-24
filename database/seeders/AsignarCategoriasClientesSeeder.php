<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\CategoriaCliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AsignarCategoriasClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenemos todos los clientes existentes
        $clientes = Cliente::all();
        
        // Obtenemos todas las categorías disponibles
        $categorias = CategoriaCliente::all();
        
        if ($categorias->isEmpty()) {
            $this->command->info('No hay categorías disponibles. Ejecute primero el CategoriaClienteSeeder.');
            return;
        }
        
        // Array con los IDs de las categorías
        $categoriaIds = $categorias->pluck('id')->toArray();
        
        // Asignamos categorías a los clientes de manera aleatoria o según algún criterio
        foreach ($clientes as $cliente) {
            // Asignamos una categoría aleatoria
            $categoriaId = $categoriaIds[array_rand($categoriaIds)];
            
            // Actualizamos el cliente con la categoría asignada
            $cliente->categoria_cliente_id = $categoriaId;
            $cliente->save();
        }
        
        $this->command->info('Categorías asignadas exitosamente a los clientes.');
    }
}

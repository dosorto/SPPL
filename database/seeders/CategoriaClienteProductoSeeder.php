<?php

namespace Database\Seeders;

use App\Models\CategoriaCliente;
use App\Models\CategoriaProducto;
use App\Models\CategoriaClienteProducto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaClienteProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenemos todas las categorías de clientes
        $categoriasClientes = CategoriaCliente::all();
        
        // Obtenemos todas las categorías de productos
        $categoriasProductos = CategoriaProducto::all();
        
        if ($categoriasClientes->isEmpty() || $categoriasProductos->isEmpty()) {
            $this->command->info('Faltan categorías de clientes o productos. Ejecute los seeders correspondientes primero.');
            return;
        }
        
        // Creamos relaciones con descuentos específicos
        $relaciones = [];
        
        // Para categoría Premium - descuentos altos
        if ($premium = $categoriasClientes->where('nombre', 'Premium')->first()) {
            foreach ($categoriasProductos as $categoriaProducto) {
                $relaciones[] = [
                    'categoria_cliente_id' => $premium->id,
                    'categoria_producto_id' => $categoriaProducto->id,
                    'descuento_porcentaje' => rand(10, 15), // 10-15% de descuento
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Para categoría Corporativo - descuentos medios-altos
        if ($corporativo = $categoriasClientes->where('nombre', 'Corporativo')->first()) {
            foreach ($categoriasProductos as $categoriaProducto) {
                $relaciones[] = [
                    'categoria_cliente_id' => $corporativo->id,
                    'categoria_producto_id' => $categoriaProducto->id,
                    'descuento_porcentaje' => rand(7, 12), // 7-12% de descuento
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Para categoría Mayorista - descuentos medios
        if ($mayorista = $categoriasClientes->where('nombre', 'Mayorista')->first()) {
            foreach ($categoriasProductos as $categoriaProducto) {
                $relaciones[] = [
                    'categoria_cliente_id' => $mayorista->id,
                    'categoria_producto_id' => $categoriaProducto->id,
                    'descuento_porcentaje' => rand(5, 10), // 5-10% de descuento
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Para categoría Regular - descuentos bajos
        if ($regular = $categoriasClientes->where('nombre', 'Regular')->first()) {
            foreach ($categoriasProductos as $categoriaProducto) {
                $relaciones[] = [
                    'categoria_cliente_id' => $regular->id,
                    'categoria_producto_id' => $categoriaProducto->id,
                    'descuento_porcentaje' => rand(2, 5), // 2-5% de descuento
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Para categoría Nuevo - sin descuentos o muy bajos
        if ($nuevo = $categoriasClientes->where('nombre', 'Nuevo')->first()) {
            foreach ($categoriasProductos as $categoriaProducto) {
                $relaciones[] = [
                    'categoria_cliente_id' => $nuevo->id,
                    'categoria_producto_id' => $categoriaProducto->id,
                    'descuento_porcentaje' => rand(0, 3), // 0-3% de descuento
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Insertamos todas las relaciones
        DB::table('categorias_clientes_productos')->insert($relaciones);
        
        $this->command->info('Relaciones entre categorías de clientes y productos creadas con éxito.');
    }
}

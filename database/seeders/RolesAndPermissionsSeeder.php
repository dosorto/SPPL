<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                // Acciones estándar (las tuyas)
        $acciones = ['ver','crear','actualizar','eliminar'];

        // Lista de módulos en tu formato actual
        $modulos = [
            //Modulos estandar
            'ventas',
            'recursos_humanos',  
            'configuraciones',
            'comercial',
            'inventario',
            'compras',
            //Modulos Premium
            'ordenes_producciones', 
            'nominas',   
            'rendimientos',
            'movimientos_inventario',
        ];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                Permission::firstOrCreate(['name' => "{$modulo}_{$accion}"]);
            }
        }
        // Definir modelos con nombres estándar
        $models = [
            'users', //1
            'roles', //2
            'paises', //3
            'departamentos', //4
            'municipios', //5
            'clientes', //6
            'proveedores', //7
            'departamento_empleados', //8
            'empleados', //9
            'categoria_unidades', //10
            'orden_compras', //11
            'productos', //13
            'tipo_empleados', //14
            'tipo_orden_compras', //15
            'unidad_de_medidas', //16
            'inventario_productos',
            'facturas', //18
            'cais',
            'caja_aperturas',
            'categoria_clientes', // NUEVO: para CategoriaCliente
            'ordenes_produccion', // NUEVO: para OrdenProduccion
        ];

        // Definir modelos con convención especial para Filament (usando ::)
        $specialModels = [
            'categoria::producto' => 'categorias_productos', // Modelo CategoriaProducto
            'subcategoria::producto' => 'subcategorias_productos', // Modelo SubcategoriaProducto
        ];
        
        // Crear y asignar permisos al rol root
        $adminRole = Role::firstOrCreate(['name' => 'root', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        // Crear y asignar permisos al rol admin
        $adminRole1 = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole1->givePermissionTo(Permission::all());

        
    }
}
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
        
        // Crear y asignar permisos al rol root
        $adminRole = Role::firstOrCreate(['name' => 'root', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        // Crear y asignar permisos al rol admin
        $adminRole1 = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole1->givePermissionTo(Permission::all());

        
    }
}
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
            'insumos_materia_prima', 
            'nominas',   
        ];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                Permission::firstOrCreate(['name' => "{$modulo}_{$accion}"]);
            }
        }

        // Crear rol de Administrador
        $adminRole = Role::where('name', 'root')->first();
        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'root']);
        }
        $adminRole1 = Role::where('name', 'admin')->first();
        if (!$adminRole1) {
            $adminRole1 = Role::create(['name' => 'admin']);
        }

        // Asignar TODOS los permisos al rol de Administrador
        $adminRole->givePermissionTo(Permission::all());
        $adminRole1->givePermissionTo(Permission::all());

        
    }
}

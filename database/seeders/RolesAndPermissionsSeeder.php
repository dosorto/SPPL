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

        // Crear permisos
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
            'personas', //12
            'productos', //13
            'tipo_empleados', //14
            'tipo_orden_compras', //15
            'unidad_de_medidas', //16
            '', //17
        ];

        // Acciones comunes de las políticas
        $actions = [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force_delete',
        ];

        // Crear permisos para cada modelo y acción
        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::create(['name' => "{$action}_{$model}"]);
            }
        }

        // Crear rol de Administrador
        $adminRole = Role::create(['name' => 'root']);
        $adminRole1 = Role::create(['name' => 'admin']);

        // Asignar TODOS los permisos al rol de Administrador
        $adminRole->givePermissionTo(Permission::all());
        $adminRole1->givePermissionTo(Permission::all());

        $roleEditor = Role::create(['name' => 'editor']);
        $roleEditor->givePermissionTo(['view_paises',]);
    }
}

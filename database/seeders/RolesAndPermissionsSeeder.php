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
            'users',
            'roles',
            'paises',
            'departamentos',
            'municipios',
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
        $adminRole = Role::create(['name' => 'admin']);

        // Asignar TODOS los permisos al rol de Administrador
        $adminRole->givePermissionTo(Permission::all());

        $roleEditor = Role::create(['name' => 'editor']);
        $roleEditor->givePermissionTo(['view_paises',]);
    }
}

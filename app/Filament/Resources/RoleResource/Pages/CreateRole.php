<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
    
    // AGREGADO: Declarar la propiedad para almacenar permisos temporalmente
    protected array $permissions = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Definir módulos y acciones directamente
        $modules = [
            'ventas',
            'recursos_humanos',  
            'configuraciones',
            'comercial',
            'inventario',
            'compras',
            'ordenes_producciones', 
            'nominas',   
        ];
        
        $actions = ['ver', 'crear', 'actualizar', 'eliminar'];
        
        // Extraer los permisos de los checkboxes
        $permissions = [];
        
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $checkboxKey = "permission_{$action}_{$module}";
                if (isset($data[$checkboxKey]) && $data[$checkboxKey]) {
                    $permissions[] = "{$module}_{$action}";
                }
                // Remover el checkbox del array de datos
                unset($data[$checkboxKey]);
            }
        }
        
        // Almacenar los permisos temporalmente
        $this->permissions = $permissions;
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // MEJORADO: Crear permisos que no existan y asignar al rol
        if (!empty($this->permissions)) {
            // Crear permisos que no existan en la base de datos
            foreach ($this->permissions as $permissionName) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }
            
            // Sincronizar los permisos con el rol
            $this->record->syncPermissions($this->permissions);
            
            // Limpiar la propiedad
            $this->permissions = [];
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rol creado exitosamente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
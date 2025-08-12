<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;
    
    // Propiedad para almacenar los permisos temporalmente
    protected array $permissions = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function () {
                    // Prevenir eliminación del rol root por no-roots
                    if ($this->record->name === 'root' && !auth()->user()->hasRole('root')) {
                        throw new \Exception('No tiene permiso para eliminar este rol.');
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Usa los mismos módulos del form - IMPORTANTE: mismo orden que en RoleResource
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

        // Hidrata los checkboxes con el estado actual de los permisos
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionName = "{$module}_{$action}";
                // CORREGIDO: usar el mismo formato que en el form
                $checkboxKey = "permission_{$action}_{$module}";
                
                // Verificar si el rol tiene este permiso
                $data[$checkboxKey] = $this->record->permissions->contains('name', $permissionName);
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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
        
        // Array para almacenar los permisos seleccionados
        $selectedPermissions = [];

        // Procesar cada módulo y acción
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $checkboxKey = "permission_{$action}_{$module}";
                
                // Si el checkbox está marcado, agregar el permiso
                if (isset($data[$checkboxKey]) && $data[$checkboxKey]) {
                    $selectedPermissions[] = "{$module}_{$action}";
                }
                
                // Remover del array de data para que no vaya al UPDATE del modelo Role
                unset($data[$checkboxKey]);
            }
        }

        // Guardar los permisos en la propiedad de la clase
        $this->permissions = $selectedPermissions;

        return $data;
    }

    protected function afterSave(): void
    {
        // Crear permisos que no existan
        foreach ($this->permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Sincronizar los permisos del rol
        $this->record->syncPermissions($this->permissions);
        
        // Limpiar la propiedad
        $this->permissions = [];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Rol actualizado exitosamente';
    }
    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
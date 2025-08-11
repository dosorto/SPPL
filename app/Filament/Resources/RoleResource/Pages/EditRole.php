<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

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
        // Definir módulos y acciones directamente
        $modules = [
            'ventas',
            'recursos_humanos',  
            'configuraciones',
            'comercial',
            'inventario',
            'compras',
            'insumos_materia_prima', 
            'nominas',   
        ];
        
        $actions = ['ver', 'crear', 'actualizar', 'eliminar'];
        
        // Cargar los permisos existentes en los checkboxes
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionName = "{$module}_{$action}";
                $checkboxKey = "permission_{$action}_{$module}";
                $data[$checkboxKey] = $this->record->hasPermissionTo($permissionName);
            }
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Definir módulos y acciones directamente
        $modules = [
            'ventas',
            'recursos_humanos',  
            'configuraciones',
            'comercial',
            'inventario',
            'compras',
            'insumos_materia_prima', 
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

    protected function afterSave(): void
    {
        // Sincronizar los permisos del rol
        if (isset($this->permissions)) {
            $this->record->syncPermissions($this->permissions);
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Rol actualizado exitosamente';
    }
    
    protected function getRedirectUrl(): string
    {
        // Después de guardar, ir al index del recurso
        return static::getResource()::getUrl('index');
    }
}
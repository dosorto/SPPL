<?php

namespace App\Filament\Resources\DepartamentoEmpleadoResource\Pages;

use App\Filament\Resources\DepartamentoEmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;

class EditDepartamentoEmpleado extends EditRecord
{
    protected static string $resource = DepartamentoEmpleadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    /**
     * Este método se ejecuta antes de guardar los cambios al editar un registro
     * Asegura que el campo empresa_id se actualice correctamente dependiendo del rol del usuario:
     * 
     * - Para usuarios root: Usa la empresa seleccionada en la sesión (current_empresa_id)
     * - Para usuarios normales: Mantiene la empresa asignada al usuario
     * 
     * Esto permite que usuarios root puedan cambiar departamentos entre empresas
     * mientras que usuarios normales solo pueden editarlos dentro de su empresa asignada.
     * 
     * Este método es necesario incluso cuando el campo es reactivo y live(), ya que
     * garantiza que el valor correcto de empresa_id se guarde en la base de datos.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si el usuario es root, usamos el valor de empresa_id de la sesión
        if (Filament::auth()->user()->hasRole('root')) {
            $data['empresa_id'] = session('current_empresa_id') ?? Filament::auth()->user()->empresa_id;
        }
        // Si no es root, asignamos la empresa del usuario
        else {
            $data['empresa_id'] = Filament::auth()->user()->empresa_id;
        }
        
        return $data;
    }
}

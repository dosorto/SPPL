<?php

namespace App\Filament\Resources\DepartamentoEmpleadoResource\Pages;

use App\Filament\Resources\DepartamentoEmpleadoResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateDepartamentoEmpleado extends CreateRecord
{
    protected static string $resource = DepartamentoEmpleadoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    /**
     * Este método se ejecuta antes de crear el registro y modifica los datos del formulario
     * Asegura que el campo empresa_id se asigne correctamente dependiendo del rol del usuario:
     * 
     * - Para usuarios root: Usa la empresa seleccionada en la sesión (current_empresa_id)
     * - Para usuarios normales: Usa la empresa asignada al usuario
     * 
     * Esto permite que usuarios root puedan crear departamentos para cualquier empresa
     * mientras que usuarios normales solo pueden crearlos para su empresa asignada.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
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

<?php

namespace App\Filament\Resources\EmpleadoDeduccionesResource\Pages;

use App\Filament\Resources\EmpleadoDeduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateEmpleadoDeducciones extends CreateRecord
{
    protected static string $resource = EmpleadoDeduccionesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();
        
        // Si el usuario es root, utilizar la empresa seleccionada en la sesión
        if ($user->hasRole('root') && session()->has('current_empresa_id')) {
            $data['empresa_id'] = session('current_empresa_id');
        } else {
            // Si no es root o no hay empresa seleccionada, usar la empresa del usuario
            $data['empresa_id'] = $user->empresa_id;
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}

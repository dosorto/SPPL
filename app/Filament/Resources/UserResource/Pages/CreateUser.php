<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');   
    }
    protected function getSavedNotificationTitle(): string
    {
        return 'Usuario Creado';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!auth()->user()->hasRole('root')) {
            // Si no es 'root', asignamos su propia 'empresa_id' al nuevo
            // usuario que se está creando.
            $data['empresa_id'] = auth()->user()->empresa_id;
        }

        // Devolvemos los datos (modificados o no) para que continúe el proceso de creación.
        return $data;
    }
    
   
}

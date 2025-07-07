<?php

namespace App\Filament\Resources\PersonaResource\Pages;

use App\Filament\Resources\PersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePersona extends CreateRecord
{
    protected static string $resource = PersonaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si no se selecciona empresa, asigna la de entorno
        if (empty($data['empresa_id'])) {
            $data['empresa_id'] = env('EMPRESA_ID');
        }
        return $data;
    }
}

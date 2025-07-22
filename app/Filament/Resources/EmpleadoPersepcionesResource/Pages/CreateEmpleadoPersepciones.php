<?php

namespace App\Filament\Resources\EmpleadoPersepcionesResource\Pages;

use App\Filament\Resources\EmpleadoPersepcionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmpleadoPersepciones extends CreateRecord
{
    protected static string $resource = EmpleadoPersepcionesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;
        return $data;
    }
}

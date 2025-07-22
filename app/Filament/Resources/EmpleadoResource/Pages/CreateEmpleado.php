<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmpleado extends CreateRecord
{
    protected static string $resource = EmpleadoResource::class;

    // cambio jessuri: Crea persona y luego el empleado
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $personaData = $data['persona'];
        $persona = \App\Models\Persona::create($personaData);
        $data['persona_id'] = $persona->id;
        unset($data['persona']);
        // Guardar deducciones seleccionadas en el campo deducciones_aplicables
        $data['deducciones_aplicables'] = $data['deducciones'] ?? [];
        unset($data['deducciones']);
        return $data;
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

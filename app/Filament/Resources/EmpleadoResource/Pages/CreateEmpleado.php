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
        // No guardar deducciones aquí, se sincronizan después de crear el empleado
        return $data;
    }

    protected function afterCreate(): void
    {
        // Sincronizar deducciones seleccionadas con la relación deducciones del empleado, agregando empresa_id en la tabla pivote
        $deducciones = $this->data['deducciones'] ?? [];
        if (!empty($deducciones)) {
            $syncData = [];
            foreach ($deducciones as $deduccionId) {
                $syncData[$deduccionId] = ['empresa_id' => $this->record->empresa_id];
            }
            $this->record->deducciones()->sync($syncData);
        }
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

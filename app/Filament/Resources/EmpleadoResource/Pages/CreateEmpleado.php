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
        // Verificar si estamos usando una persona existente o creando una nueva
        if (isset($data['persona_autocompletada']) && $data['persona_autocompletada']) {
            // Buscar la persona existente por DNI
            $persona = \App\Models\Persona::where('dni', $data['persona']['dni'])->first();
            
            if (!$persona) {
                throw new \Exception('No se encontró la persona autocompletada. Por favor, intente nuevamente.');
            }
            
            $data['persona_id'] = $persona->id;
        } else {
            // Crear una nueva persona
            $personaData = $data['persona'];
            $persona = \App\Models\Persona::create($personaData);
            $data['persona_id'] = $persona->id;
        }
        
        // Eliminar los datos de persona y el flag de autocompletado
        unset($data['persona']);
        unset($data['persona_autocompletada']);
        
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

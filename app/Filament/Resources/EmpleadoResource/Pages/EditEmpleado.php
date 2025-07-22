<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmpleado extends EditRecord
{
    protected static string $resource = EmpleadoResource::class;



    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Prellena los campos del wizard con los datos relacionados de persona y empleado
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $empleado = $this->record;
        if ($empleado && $empleado->persona) {
            $data['persona'] = $empleado->persona->toArray();
        }
        // Prellenar deducciones seleccionadas
        $data['deducciones'] = $empleado->deduccionesAplicadas()->pluck('deduccion_id')->toArray();
        return $data;
    }

    // cambio jessuri: Actualiza persona y luego el empleado
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $empleado = $this->record;
        $personaData = $data['persona'];
        $empleado->persona->update($personaData);
        $data['persona_id'] = $empleado->persona->id;
        unset($data['persona']);
        // Guardar deducciones seleccionadas en el campo deducciones_aplicables
        $data['deducciones_aplicables'] = $data['deducciones'] ?? [];
        unset($data['deducciones']);
        return $data;
    }

    // Ya no se sincronizan registros en EmpleadoDeducciones automáticamente aquí

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

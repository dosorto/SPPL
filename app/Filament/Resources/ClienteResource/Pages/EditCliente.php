<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getForm(string $name = 'form'): ?\Filament\Forms\Form
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        
        return $resource::getEditForm($this->makeForm())
            ->model($record)
            ->statePath('data');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los datos de la persona relacionada en el formulario
        if ($this->record->persona) {
            $data['persona']['dni'] = $this->record->persona->dni;
            $data['persona']['primer_nombre'] = $this->record->persona->primer_nombre;
            $data['persona']['segundo_nombre'] = $this->record->persona->segundo_nombre;
            $data['persona']['primer_apellido'] = $this->record->persona->primer_apellido;
            $data['persona']['segundo_apellido'] = $this->record->persona->segundo_apellido;
            $data['persona']['sexo'] = $this->record->persona->sexo;
            $data['persona']['fecha_nacimiento'] = $this->record->persona->fecha_nacimiento;
            $data['persona']['tipo_persona'] = $this->record->persona->tipo_persona;
            $data['persona']['telefono'] = $this->record->persona->telefono;
            $data['persona']['direccion'] = $this->record->persona->direccion;
            $data['persona']['pais_id'] = $this->record->persona->pais_id;
            $data['persona']['departamento_id'] = $this->record->persona->departamento_id;
            $data['persona']['municipio_id'] = $this->record->persona->municipio_id;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extraer los datos de persona para guardarlos por separado
        $personaData = $data['persona'] ?? [];
        unset($data['persona']);

        // Guardar los datos de persona
        if (!empty($personaData) && $this->record->persona) {
            $this->record->persona->update($personaData);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}

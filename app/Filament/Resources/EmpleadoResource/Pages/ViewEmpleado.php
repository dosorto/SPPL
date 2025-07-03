<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewEmpleado extends ViewRecord
{
    protected static string $resource = EmpleadoResource::class;

    // Prellena los campos del wizard con los datos relacionados de persona y empleado
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $empleado = $this->record;
        if ($empleado && $empleado->persona) {
            $data['persona'] = $empleado->persona->toArray();
        }
        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Wizard::make([
                // Paso 1: Datos personales
                \Filament\Forms\Components\Wizard\Step::make('Datos personales')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('persona.primer_nombre')->label('Primer nombre')->disabled(),
                        \Filament\Forms\Components\TextInput::make('persona.segundo_nombre')->label('Segundo nombre')->disabled(),
                        \Filament\Forms\Components\TextInput::make('persona.primer_apellido')->label('Primer apellido')->disabled(),
                        \Filament\Forms\Components\TextInput::make('persona.segundo_apellido')->label('Segundo apellido')->disabled(),
                        \Filament\Forms\Components\TextInput::make('persona.dni')->label('DNI')->disabled(),
                        \Filament\Forms\Components\TextInput::make('persona.direccion')->label('Dirección')->disabled(),
                        \Filament\Forms\Components\TextInput::make('persona.telefono')->label('Teléfono')->disabled(),
                        \Filament\Forms\Components\TextInput::make('persona.sexo')->label('Sexo')->disabled(),
                        \Filament\Forms\Components\DatePicker::make('persona.fecha_nacimiento')->label('Fecha de nacimiento')->disabled(),
                    ]),
                // Paso 2: Datos de empleado
                \Filament\Forms\Components\Wizard\Step::make('Datos de empleado')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('numero_empleado')->label('Número de empleado')->disabled(),
                        \Filament\Forms\Components\DatePicker::make('fecha_ingreso')->label('Fecha de ingreso')->disabled(),
                        \Filament\Forms\Components\TextInput::make('salario')->label('Salario')->disabled(),
                        \Filament\Forms\Components\TextInput::make('tipoEmpleado.nombre_tipo')->label('Tipo de empleado')->disabled(),
                    ]),
                // Paso 3: Empresa y departamento
                \Filament\Forms\Components\Wizard\Step::make('Empresa y departamento')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('empresa.nombre')->label('Empresa')->disabled(),
                        \Filament\Forms\Components\TextInput::make('departamento.nombre_departamento_empleado')->label('Departamento')->disabled(),
                    ]),
            ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

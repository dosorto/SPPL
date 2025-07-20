<?php

namespace App\Filament\Resources\PersonaResource\Pages;

use App\Filament\Resources\PersonaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Components;

class ViewPersona extends ViewRecord
{
    protected static string $resource = PersonaResource::class;

    public function getForm(string $name = 'form'): ?\Filament\Forms\Form
    {
        $record = $this->getRecord();
        $empresa = optional($record->empresa)->nombre ?? 'No asignada';
        $persona = $record;
        $pais = optional($persona->pais)->nombre_pais ?? '';
        $departamento = optional($persona->municipio->departamento)->nombre_departamento ?? '';
        $municipio = optional($persona->municipio)->nombre_municipio ?? '';

        return $this->makeForm()
            ->schema([
                Components\Section::make('Datos de Cliente')
                    ->schema([
                        Components\Placeholder::make('numero_cliente')
                            ->label('Número de Cliente')
                            ->content($record->numero_cliente ?? ''),
                        Components\Placeholder::make('rtn')
                            ->label('RTN')
                            ->content($record->RTN ?? ''),
                        Components\Placeholder::make('empresa')
                            ->label('Empresa')
                            ->content($empresa),
                    ]),
                Components\Section::make('Datos de Persona')
                    ->schema([
                        Components\Placeholder::make('dni')
                            ->label('DNI')
                            ->content($persona->dni ?? ''),
                        Components\Placeholder::make('nombres')
                            ->label('Nombres')
                            ->content(trim(($persona->primer_nombre ?? '') . ' ' . ($persona->segundo_nombre ?? ''))),
                        Components\Placeholder::make('apellidos')
                            ->label('Apellidos')
                            ->content(trim(($persona->primer_apellido ?? '') . ' ' . ($persona->segundo_apellido ?? ''))),
                        Components\Placeholder::make('sexo')
                            ->label('Sexo')
                            ->content($persona->sexo ?? ''),
                        Components\Placeholder::make('fecha_nacimiento')
                            ->label('Fecha de nacimiento')
                            ->content($persona->fecha_nacimiento ?? ''),
                    ]),
                Components\Section::make('Dirección')
                    ->schema([
                        Components\Placeholder::make('pais')
                            ->label('País')
                            ->content($pais),
                        Components\Placeholder::make('departamento')
                            ->label('Departamento')
                            ->content($departamento),
                        Components\Placeholder::make('municipio')
                            ->label('Municipio')
                            ->content($municipio),
                        Components\Placeholder::make('direccion')
                            ->label('Dirección')
                            ->content($persona->direccion ?? ''),
                        Components\Placeholder::make('telefono')
                            ->label('Teléfono')
                            ->content($persona->telefono ?? ''),
                    ]),
            ])
            ->model($record)
            ->statePath('data');
    }
}

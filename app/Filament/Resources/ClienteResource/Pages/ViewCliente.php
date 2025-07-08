<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;

class ViewCliente extends ViewRecord
{
    protected static string $resource = ClienteResource::class;

    public function getForm(string $name = 'form'): ?\Filament\Forms\Form
    {
        $record = $this->getRecord();
        $empresa = optional($record->empresa)->nombre ?? 'No asignada';
        $persona = optional($record->persona);
        $pais = optional($persona->pais)->nombre_pais ?? '';
        $departamento = optional($persona->municipio->departamento)->nombre_departamento ?? '';
        $municipio = optional($persona->municipio)->nombre_municipio ?? '';

        return $this->makeForm()
            ->schema([
                Forms\Components\Section::make('Datos de Cliente')
                    ->schema([
                        Forms\Components\Placeholder::make('numero_cliente')
                            ->label('Número de Cliente')
                            ->content($record->numero_cliente),
                        Forms\Components\Placeholder::make('rtn')
                            ->label('RTN')
                            ->content($record->RTN),
                        Forms\Components\Placeholder::make('empresa')
                            ->label('Empresa')
                            ->content($empresa),
                    ]),
                Forms\Components\Section::make('Datos de Persona')
                    ->schema([
                        Forms\Components\Placeholder::make('dni')
                            ->label('DNI')
                            ->content($persona->dni ?? ''),
                        Forms\Components\Placeholder::make('nombres')
                            ->label('Nombres')
                            ->content(trim(($persona->primer_nombre ?? '') . ' ' . ($persona->segundo_nombre ?? ''))),
                        Forms\Components\Placeholder::make('apellidos')
                            ->label('Apellidos')
                            ->content(trim(($persona->primer_apellido ?? '') . ' ' . ($persona->segundo_apellido ?? ''))),
                        Forms\Components\Placeholder::make('sexo')
                            ->label('Sexo')
                            ->content($persona->sexo ?? ''),
                        Forms\Components\Placeholder::make('fecha_nacimiento')
                            ->label('Fecha de nacimiento')
                            ->content($persona->fecha_nacimiento ?? ''),
                    ]),
                Forms\Components\Section::make('Dirección')
                    ->schema([
                        Forms\Components\Placeholder::make('pais')
                            ->label('País')
                            ->content($pais),
                        Forms\Components\Placeholder::make('departamento')
                            ->label('Departamento')
                            ->content($departamento),
                        Forms\Components\Placeholder::make('municipio')
                            ->label('Municipio')
                            ->content($municipio),
                        Forms\Components\Placeholder::make('direccion')
                            ->label('Dirección')
                            ->content($persona->direccion ?? ''),
                        Forms\Components\Placeholder::make('telefono')
                            ->label('Teléfono')
                            ->content($persona->telefono ?? ''),
                    ]),
            ])
            ->model($record)
            ->statePath('data');
    }
}

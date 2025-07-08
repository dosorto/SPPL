<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Pages\Actions\EditAction;

class ViewEmpleado extends ViewRecord
{
    protected static string $resource = EmpleadoResource::class;

    // Este método asegura que se use el esquema definido aquí
    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->columns(2);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Datos personales')
                ->icon('heroicon-o-user')
                ->schema([
                    Placeholder::make('dni')
                        ->label('DNI')
                        ->content(fn () => $this->record->persona?->dni ?? 'N/A'),

                    Placeholder::make('primer_nombre')
                        ->label('Primer nombre')
                        ->content(fn () => $this->record->persona?->primer_nombre ?? 'N/A'),

                    Placeholder::make('segundo_nombre')
                        ->label('Segundo nombre')
                        ->content(fn () => $this->record->persona?->segundo_nombre ?? 'N/A'),

                    Placeholder::make('primer_apellido')
                        ->label('Primer apellido')
                        ->content(fn () => $this->record->persona?->primer_apellido ?? 'N/A'),

                    Placeholder::make('segundo_apellido')
                        ->label('Segundo apellido')
                        ->content(fn () => $this->record->persona?->segundo_apellido ?? 'N/A'),

                    Placeholder::make('sexo')
                        ->label('Sexo')
                        ->content(fn () => $this->record->persona?->sexo ?? 'N/A'),

                    Placeholder::make('fecha_nacimiento')
                        ->label('Fecha de nacimiento')
                        ->content(function () {
                            $fecha = $this->record->persona?->fecha_nacimiento;

                            if (!$fecha) {
                                return 'N/A';
                            }

                            if (method_exists($fecha, 'format')) {
                                return $fecha->format('d/m/Y');
                            }

                            try {
                                return \Carbon\Carbon::parse($fecha)->format('d/m/Y');
                            } catch (\Exception $e) {
                                return $fecha;
                            }
                        }),
                    Placeholder::make('direccion')
                        ->label('Dirección')
                        ->content(fn () => $this->record->persona?->direccion ?? 'N/A'),

                    Placeholder::make('telefono')
                        ->label('Teléfono')
                        ->content(fn () => $this->record->persona?->telefono ?? 'N/A'),

                    Placeholder::make('pais')
                        ->label('País')
                        ->content(fn () => $this->record->persona?->pais?->nombre_pais ?? 'N/A'),

                    Placeholder::make('departamento')
                        ->label('Departamento')
                        ->content(fn () => $this->record->persona?->departamento?->nombre_departamento ?? 'N/A'),

                    Placeholder::make('municipio')
                        ->label('Municipio')
                        ->content(fn () => $this->record->persona?->municipio?->nombre_municipio ?? 'N/A'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Datos de empleado')
                ->icon('heroicon-o-briefcase')
                ->schema([
                    Placeholder::make('numero_empleado')
                        ->label('Número de empleado')
                        ->content(fn () => $this->record->numero_empleado ?? 'N/A'),

                    Placeholder::make('fecha_ingreso')
                        ->label('Fecha de ingreso')
                        ->content(fn () => optional($this->record->fecha_ingreso)->format('d/m/Y') ?? 'N/A'),

                    Placeholder::make('salario')
                        ->label('Salario')
                        ->content(fn () => number_format($this->record->salario ?? 0, 2)),

                    Placeholder::make('tipo_empleado')
                        ->label('Tipo de empleado')
                        ->content(fn () => $this->record->tipoEmpleado?->nombre_tipo ?? 'N/A'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Empresa y departamento')
                ->icon('heroicon-o-building-office')
                ->schema([
                    Placeholder::make('empresa')
                        ->label('Empresa')
                        ->content(fn () => $this->record->empresa?->nombre ?? 'N/A'),

                    Placeholder::make('departamento_empleado')
                        ->label('Departamento')
                        ->content(fn () => $this->record->departamento?->nombre_departamento_empleado ?? 'N/A'),
                ])
                ->columns(2)
                ->collapsible(),
                
        ];  
        
    }

    protected function getHeaderActions(): array
        {
            return [
                EditAction::make(),
            ];
        }
}

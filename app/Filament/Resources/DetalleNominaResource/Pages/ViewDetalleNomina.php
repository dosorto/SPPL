<?php

namespace App\Filament\Resources\DetalleNominaResource\Pages;

use App\Filament\Resources\DetalleNominaResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model; // 

class ViewDetalleNomina extends ViewRecord
{
    protected static string $resource = DetalleNominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function resolveRecord(int | string $key): Model
    {
        return parent::resolveRecord($key)->load('empleado.persona');
    }
    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema($this->getFormSchema());
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Datos del empleado')
                ->icon('heroicon-o-user')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('numero_empleado')
                        ->label('Número de empleado')
                        ->content(fn () => $this->record->empleado?->numero_empleado ?? 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('primer_nombre')
                        ->label('Primer nombre')
                        ->content(fn () => $this->record->empleado?->persona?->primer_nombre ?? 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('segundo_nombre')
                        ->label('Segundo nombre')
                        ->content(fn () => $this->record->empleado?->persona?->segundo_nombre ?? 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('primer_apellido')
                        ->label('Primer apellido')
                        ->content(fn () => $this->record->empleado?->persona?->primer_apellido ?? 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('segundo_apellido')
                        ->label('Segundo apellido')
                        ->content(fn () => $this->record->empleado?->persona?->segundo_apellido ?? 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('telefono')
                        ->label('Teléfono')
                        ->content(fn () => $this->record->empleado?->persona?->telefono ?? 'N/A'),
                ])
                ->columns(2)
                ->collapsible(),

            \Filament\Forms\Components\Section::make('Empresa y departamento')
                ->icon('heroicon-o-building-office')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('empresa')
                        ->label('Empresa')
                        ->content(fn () => $this->record->empleado?->empresa?->nombre ?? 'N/A'),
                    \Filament\Forms\Components\Placeholder::make('departamento_empleado')
                        ->label('Departamento')
                        ->content(fn () => $this->record->empleado?->departamento?->nombre_departamento_empleado ?? 'N/A'),
                ])
                ->columns(2)
                ->collapsible(),

            \Filament\Forms\Components\Section::make('Detalle de nómina')
                ->icon('heroicon-o-currency-dollar')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('sueldo')
                        ->label('Sueldo')
                        ->content(fn () => 'L. ' . number_format($this->record->sueldo_bruto ?? 0, 2)),
                    \Filament\Forms\Components\Placeholder::make('percepciones')
                        ->label('Percepciones')
                        ->content(fn () => 'L. ' . number_format($this->record->percepciones ?? 0, 2)),
                    \Filament\Forms\Components\Placeholder::make('deducciones')
                        ->label('Deducciones')
                        ->content(fn () => 'L. ' . number_format($this->record->deducciones ?? 0, 2)),
                    \Filament\Forms\Components\Placeholder::make('sueldo_neto')
                        ->label('Sueldo Neto')
                        ->content(fn () => 'L. ' . number_format($this->record->sueldo_neto ?? 0, 2)),
                ])
                ->columns(2)
                ->collapsible(),
        ];
    }
}

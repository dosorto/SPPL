<?php

namespace App\Filament\Resources\EmpleadoPersepcionesResource\Pages;

use App\Filament\Resources\EmpleadoPersepcionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmpleadoPersepciones extends EditRecord
{
    protected static string $resource = EmpleadoPersepcionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        $schema = parent::getFormSchema();
        $schema[] = \Filament\Forms\Components\TextInput::make('cantidad_horas')
            ->label('Cantidad de horas extras')
            ->disabled()
            ->visible(fn ($get) => optional(\App\Models\Percepciones::find($get('percepcion_id')))->percepcion === 'Horas Extras');
        return $schema;
    }
}

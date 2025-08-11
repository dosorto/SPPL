<?php

namespace App\Filament\Resources\EmpleadoPersepcionesResource\Pages;

use App\Filament\Resources\EmpleadoPersepcionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;

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
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Filament::auth()->user();
        
        // Si el usuario es root, utilizar la empresa seleccionada en la sesión
        if ($user->hasRole('root') && session()->has('current_empresa_id')) {
            $data['empresa_id'] = session('current_empresa_id');
        } else {
            // Si no es root o no hay empresa seleccionada, usar la empresa del usuario
            $data['empresa_id'] = $user->empresa_id;
        }
        
        return $data;
    }
}

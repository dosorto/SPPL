<?php

namespace App\Filament\Resources\DeduccionesResource\Pages;

use App\Filament\Resources\DeduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;

class EditDeducciones extends EditRecord
{
    protected static string $resource = DeduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
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

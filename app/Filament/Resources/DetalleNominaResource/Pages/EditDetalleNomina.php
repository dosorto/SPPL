<?php

namespace App\Filament\Resources\DetalleNominaResource\Pages;

use App\Filament\Resources\DetalleNominaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;

class EditDetalleNomina extends EditRecord
{
    protected static string $resource = DetalleNominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si el usuario es root, usamos el valor de empresa_id de la sesión
        if (Filament::auth()->user()->hasRole('root')) {
            $data['empresa_id'] = session('current_empresa_id') ?? Filament::auth()->user()->empresa_id;
        }
        // Si no es root, asignamos la empresa del usuario
        else {
            $data['empresa_id'] = Filament::auth()->user()->empresa_id;
        }
        
        return $data;
    }
}

<?php

namespace App\Filament\Resources\DetalleNominaResource\Pages;

use App\Filament\Resources\DetalleNominaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
        // Si no se proporciona empresa_id, obtenerlo de la nómina
        if (empty($data['empresa_id']) && !empty($data['nomina_id'])) {
            $nomina = \App\Models\Nominas::find($data['nomina_id']);
            if ($nomina) {
                $data['empresa_id'] = $nomina->empresa_id;
            }
        }
        
        return $data;
    }
}

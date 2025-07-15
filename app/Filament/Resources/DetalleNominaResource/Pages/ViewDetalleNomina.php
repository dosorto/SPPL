<?php

namespace App\Filament\Resources\DetalleNominaResource\Pages;

use App\Filament\Resources\DetalleNominaResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model; // ✅ Esta línea es la clave

class ViewDetalleNomina extends ViewRecord
{
    protected static string $resource = DetalleNominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make()
                ->label('Editar'),
        ];
    }

    protected function resolveRecord(int | string $key): Model
    {
        return parent::resolveRecord($key)->load('empleado.persona');
    }
}

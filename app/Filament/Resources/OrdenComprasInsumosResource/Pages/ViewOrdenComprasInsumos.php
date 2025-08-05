<?php

namespace App\Filament\Resources\OrdenComprasInsumosResource\Pages;

use App\Filament\Resources\OrdenComprasInsumosResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrdenComprasInsumos extends ViewRecord
{
    protected static string $resource = OrdenComprasInsumosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->disabled(fn ($record) => $record->estado === 'Recibida'),
            Actions\DeleteAction::make()->disabled(fn ($record) => $record->estado === 'Recibida'),
        ];
    }
}
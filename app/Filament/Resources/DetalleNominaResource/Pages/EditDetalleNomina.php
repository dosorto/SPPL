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
}

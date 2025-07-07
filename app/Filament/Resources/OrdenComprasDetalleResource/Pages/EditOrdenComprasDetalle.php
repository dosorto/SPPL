<?php

namespace App\Filament\Resources\OrdenComprasDetalleResource\Pages;

use App\Filament\Resources\OrdenComprasDetalleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdenComprasDetalle extends EditRecord
{
    protected static string $resource = OrdenComprasDetalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

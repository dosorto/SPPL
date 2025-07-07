<?php

namespace App\Filament\Resources\OrdenComprasDetalleResource\Pages;

use App\Filament\Resources\OrdenComprasDetalleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrdenComprasDetalles extends ListRecords
{
    protected static string $resource = OrdenComprasDetalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\OrdenComprasInsumosResource\Pages;

use App\Filament\Resources\OrdenComprasInsumosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrdenComprasInsumos extends ListRecords
{
    protected static string $resource = OrdenComprasInsumosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

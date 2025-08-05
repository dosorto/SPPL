<?php

namespace App\Filament\Resources\InventarioInsumosResource\Pages;

use App\Filament\Resources\InventarioInsumosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventarioInsumos extends ListRecords
{
    protected static string $resource = InventarioInsumosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

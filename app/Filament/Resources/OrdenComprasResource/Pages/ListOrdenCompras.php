<?php

namespace App\Filament\Resources\OrdenComprasResource\Pages;

use App\Filament\Resources\OrdenComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrdenCompras extends ListRecords
{
    protected static string $resource = OrdenComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

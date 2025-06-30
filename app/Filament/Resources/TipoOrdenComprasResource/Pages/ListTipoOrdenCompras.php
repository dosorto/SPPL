<?php

namespace App\Filament\Resources\TipoOrdenComprasResource\Pages;

use App\Filament\Resources\TipoOrdenComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoOrdenCompras extends ListRecords
{
    protected static string $resource = TipoOrdenComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

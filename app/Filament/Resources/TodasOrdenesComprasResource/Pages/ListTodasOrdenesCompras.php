<?php

namespace App\Filament\Resources\TodasOrdenesComprasResource\Pages;

use App\Filament\Resources\TodasOrdenesComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTodasOrdenesCompras extends ListRecords
{
    protected static string $resource = TodasOrdenesComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

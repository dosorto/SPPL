<?php

namespace App\Filament\Resources\SubcategoriaProductoResource\Pages;

use App\Filament\Resources\SubcategoriaProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubcategoriaProductos extends ListRecords
{
    protected static string $resource = SubcategoriaProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

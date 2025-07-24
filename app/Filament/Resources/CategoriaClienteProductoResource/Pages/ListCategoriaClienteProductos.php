<?php

namespace App\Filament\Resources\CategoriaClienteProductoResource\Pages;

use App\Filament\Resources\CategoriaClienteProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaClienteProductos extends ListRecords
{
    protected static string $resource = CategoriaClienteProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

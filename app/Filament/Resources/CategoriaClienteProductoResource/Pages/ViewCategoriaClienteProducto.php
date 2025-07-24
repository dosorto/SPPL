<?php

namespace App\Filament\Resources\CategoriaClienteProductoResource\Pages;

use App\Filament\Resources\CategoriaClienteProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoriaClienteProducto extends ViewRecord
{
    protected static string $resource = CategoriaClienteProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

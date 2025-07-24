<?php

namespace App\Filament\Resources\CategoriaClienteProductoResource\Pages;

use App\Filament\Resources\CategoriaClienteProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaClienteProducto extends EditRecord
{
    protected static string $resource = CategoriaClienteProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

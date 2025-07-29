<?php

namespace App\Filament\Resources\SubcategoriaProductoResource\Pages;

use App\Filament\Resources\SubcategoriaProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubcategoriaProducto extends EditRecord
{
    protected static string $resource = SubcategoriaProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

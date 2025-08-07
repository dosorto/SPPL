<?php

namespace App\Filament\Resources\CategoriaProductoResource\Pages;

use App\Filament\Resources\CategoriaProductoResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoriaProductos extends ViewRecord
{
    protected static string $resource = CategoriaProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make()->label('Editar'),
            \Filament\Actions\DeleteAction::make()->label('Eliminar'),
        ];
    }
}
<?php

namespace App\Filament\Resources\InventarioProductosResource\Pages;

use App\Filament\Resources\InventarioProductosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventarioProductos extends EditRecord
{
    protected static string $resource = InventarioProductosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

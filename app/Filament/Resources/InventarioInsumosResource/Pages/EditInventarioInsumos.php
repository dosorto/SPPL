<?php

namespace App\Filament\Resources\InventarioInsumosResource\Pages;

use App\Filament\Resources\InventarioInsumosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventarioInsumos extends EditRecord
{
    protected static string $resource = InventarioInsumosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

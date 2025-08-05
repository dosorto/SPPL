<?php

namespace App\Filament\Resources\TodasOrdenesComprasResource\Pages;

use App\Filament\Resources\TodasOrdenesComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTodasOrdenesCompras extends EditRecord
{
    protected static string $resource = TodasOrdenesComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

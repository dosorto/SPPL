<?php

namespace App\Filament\Resources\OrdenComprasResource\Pages;

use App\Filament\Resources\OrdenComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdenCompras extends EditRecord
{
    protected static string $resource = OrdenComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

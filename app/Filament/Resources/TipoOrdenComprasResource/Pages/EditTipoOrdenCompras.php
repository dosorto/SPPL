<?php

namespace App\Filament\Resources\TipoOrdenComprasResource\Pages;

use App\Filament\Resources\TipoOrdenComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoOrdenCompras extends EditRecord
{
    protected static string $resource = TipoOrdenComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

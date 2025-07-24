<?php

namespace App\Filament\Resources\CategoriaClienteResource\Pages;

use App\Filament\Resources\CategoriaClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoriaCliente extends ViewRecord
{
    protected static string $resource = CategoriaClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

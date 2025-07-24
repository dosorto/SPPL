<?php

namespace App\Filament\Resources\CategoriaClienteResource\Pages;

use App\Filament\Resources\CategoriaClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaClientes extends ListRecords
{
    protected static string $resource = CategoriaClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

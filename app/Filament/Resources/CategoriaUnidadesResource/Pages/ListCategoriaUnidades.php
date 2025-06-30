<?php

namespace App\Filament\Resources\CategoriaUnidadesResource\Pages;

use App\Filament\Resources\CategoriaUnidadesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaUnidades extends ListRecords
{
    protected static string $resource = CategoriaUnidadesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

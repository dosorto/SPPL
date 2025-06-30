<?php

namespace App\Filament\Resources\CategoriaUnidadesResource\Pages;

use App\Filament\Resources\CategoriaUnidadesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaUnidades extends EditRecord
{
    protected static string $resource = CategoriaUnidadesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

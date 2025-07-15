<?php

namespace App\Filament\Resources\DeduccionesResource\Pages;

use App\Filament\Resources\DeduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeducciones extends ListRecords
{
    protected static string $resource = DeduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

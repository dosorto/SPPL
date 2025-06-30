<?php

namespace App\Filament\Resources\UnidadDeMedidasResource\Pages;

use App\Filament\Resources\UnidadDeMedidasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnidadDeMedidas extends ListRecords
{
    protected static string $resource = UnidadDeMedidasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNominas extends ListRecords
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

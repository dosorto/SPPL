<?php

namespace App\Filament\Resources\CaiResource\Pages;

use App\Filament\Resources\CaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCais extends ListRecords
{
    protected static string $resource = CaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

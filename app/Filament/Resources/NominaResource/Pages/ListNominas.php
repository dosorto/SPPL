<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNominas extends ListRecords
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->orderBy('created_at', 'desc');
    }
}

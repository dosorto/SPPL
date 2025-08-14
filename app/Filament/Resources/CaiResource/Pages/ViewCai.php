<?php

namespace App\Filament\Resources\CaiResource\Pages;

use App\Filament\Resources\CaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCai extends ViewRecord
{
    protected static string $resource = CaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('volver')
                ->label('Volver a la lista')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl('index'))
                ->color('primary'),
        ];
    }
}

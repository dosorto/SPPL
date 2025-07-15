<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNomina extends EditRecord
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

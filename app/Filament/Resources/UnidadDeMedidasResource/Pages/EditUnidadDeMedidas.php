<?php

namespace App\Filament\Resources\UnidadDeMedidasResource\Pages;

use App\Filament\Resources\UnidadDeMedidasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnidadDeMedidas extends EditRecord
{
    protected static string $resource = UnidadDeMedidasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

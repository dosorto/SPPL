<?php

namespace App\Filament\Resources\CajaAperturaResource\Pages;

use App\Filament\Resources\CajaAperturaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCajaApertura extends EditRecord
{
    protected static string $resource = CajaAperturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

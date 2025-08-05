<?php

namespace App\Filament\Resources\FacturaCajaResource\Pages;

use App\Filament\Resources\FacturaCajaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacturaCaja extends EditRecord
{
    protected static string $resource = FacturaCajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

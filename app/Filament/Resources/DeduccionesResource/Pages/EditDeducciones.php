<?php

namespace App\Filament\Resources\DeduccionesResource\Pages;

use App\Filament\Resources\DeduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeducciones extends EditRecord
{
    protected static string $resource = DeduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

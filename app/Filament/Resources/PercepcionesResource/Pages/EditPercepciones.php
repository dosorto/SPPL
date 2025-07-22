<?php

namespace App\Filament\Resources\PercepcionesResource\Pages;

use App\Filament\Resources\PercepcionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPercepciones extends EditRecord
{
    protected static string $resource = PercepcionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

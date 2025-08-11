<?php

namespace App\Filament\Resources\OrdenProduccionResource\Pages;

use App\Filament\Resources\OrdenProduccionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrdenProduccion extends CreateRecord
{
    protected static string $resource = OrdenProduccionResource::class;

    protected function getCreatedNotificationRedirectUrl(): string
    {
        return static::$resource::getUrl('view', ['record' => $this->getRecord()]);
    }
}

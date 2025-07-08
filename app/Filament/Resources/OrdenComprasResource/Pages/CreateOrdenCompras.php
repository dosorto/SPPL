<?php

namespace App\Filament\Resources\OrdenComprasResource\Pages;

use App\Filament\Resources\OrdenComprasResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateOrdenCompras extends CreateRecord
{
    protected static string $resource = OrdenComprasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extraer los datos anidados del wizard
        $formData = $data['data'] ?? $data;

        // Asegurar que los campos de auditoría estén presentes
        $formData['created_by'] = $formData['created_by'] ?? auth()->id();
        $formData['updated_by'] = $formData['updated_by'] ?? auth()->id();

        return $formData;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Orden de Compra y Detalles guardados')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

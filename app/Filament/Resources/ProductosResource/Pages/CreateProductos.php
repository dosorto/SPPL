<?php

namespace App\Filament\Resources\ProductosResource\Pages;

use App\Filament\Resources\ProductosResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ProductoFoto;

class CreateProductos extends CreateRecord
{
    protected static string $resource = ProductosResource::class;

    protected function getFormSubmitButtonLabel(): string
    {
        return 'Guardar producto';
    }

    public function getTitle(): string
    {
        return 'Nuevo producto';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extraer las fotos para guardarlas después
        $this->fotosTemporales = $data['fotos'] ?? [];

        // Evitar que se intente guardar 'fotos' directamente en la tabla productos (no existe esa columna en tu caso)
        unset($data['fotos']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Luego de crear el producto, guardar las fotos asociadas
        foreach ($this->fotosTemporales as $foto) {
            ProductoFoto::create([
                'producto_id' => $this->record->id,
                'url' => is_string($foto) ? $foto : (is_array($foto) ? $foto['name'] ?? '' : ''),
            ]);
        }
    }
}

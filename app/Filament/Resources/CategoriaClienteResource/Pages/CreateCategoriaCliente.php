<?php

namespace App\Filament\Resources\CategoriaClienteResource\Pages;

use App\Filament\Resources\CategoriaClienteResource;
use App\Models\CategoriaClienteProducto;
use App\Models\CategoriaClienteProductoEspecifico;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoriaCliente extends CreateRecord
{
    protected static string $resource = CategoriaClienteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extraer los descuentos antes de crear la categoría
        $this->descuentos = $data['categorias_productos_descuentos'] ?? [];
        $this->productosEspecificos = $data['productos_especificos_descuentos'] ?? [];
        
        unset($data['categorias_productos_descuentos']);
        unset($data['productos_especificos_descuentos']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Crear descuentos por categoría de producto
        if (!empty($this->descuentos)) {
            foreach ($this->descuentos as $descuento) {
                CategoriaClienteProducto::create([
                    'categoria_cliente_id' => $this->record->id,
                    'categoria_producto_id' => $descuento['categoria_producto_id'],
                    'descuento_porcentaje' => $descuento['descuento_porcentaje'],
                    'activo' => $descuento['activo'] ?? true,
                ]);
            }
        }

        // Crear descuentos por productos específicos
        if (!empty($this->productosEspecificos)) {
            foreach ($this->productosEspecificos as $producto) {
                CategoriaClienteProductoEspecifico::create([
                    'categoria_cliente_id' => $this->record->id,
                    'productos_id' => $producto['productos_id'],
                    'descuento_porcentaje' => $producto['descuento_porcentaje'],
                    'activo' => $producto['activo'] ?? true,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    private $descuentos = [];
    private $productosEspecificos = [];
}

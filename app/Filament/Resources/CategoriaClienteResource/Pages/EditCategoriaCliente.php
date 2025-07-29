<?php

namespace App\Filament\Resources\CategoriaClienteResource\Pages;

use App\Filament\Resources\CategoriaClienteResource;
use App\Models\CategoriaClienteProducto;
use App\Models\CategoriaClienteProductoEspecifico;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaCliente extends EditRecord
{
    protected static string $resource = CategoriaClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los descuentos por categoría existentes
        $descuentos = CategoriaClienteProducto::where('categoria_cliente_id', $this->record->id)
            ->get()
            ->map(function ($item) {
                return [
                    'categoria_producto_id' => $item->categoria_producto_id,
                    'descuento_porcentaje' => $item->descuento_porcentaje,
                    'activo' => $item->activo,
                ];
            })
            ->toArray();

        // Cargar los descuentos por productos específicos existentes
        $productosEspecificos = CategoriaClienteProductoEspecifico::where('categoria_cliente_id', $this->record->id)
            ->get()
            ->map(function ($item) {
                return [
                    'productos_id' => $item->productos_id,
                    'descuento_porcentaje' => $item->descuento_porcentaje,
                    'activo' => $item->activo,
                ];
            })
            ->toArray();

        $data['categorias_productos_descuentos'] = $descuentos;
        $data['productos_especificos_descuentos'] = $productosEspecificos;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extraer los descuentos antes de actualizar la categoría
        $this->descuentos = $data['categorias_productos_descuentos'] ?? [];
        $this->productosEspecificos = $data['productos_especificos_descuentos'] ?? [];
        
        unset($data['categorias_productos_descuentos']);
        unset($data['productos_especificos_descuentos']);
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Eliminar descuentos existentes
        CategoriaClienteProducto::where('categoria_cliente_id', $this->record->id)->delete();
        CategoriaClienteProductoEspecifico::where('categoria_cliente_id', $this->record->id)->delete();
        
        // Crear nuevos descuentos por categoría
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

        // Crear nuevos descuentos por productos específicos
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

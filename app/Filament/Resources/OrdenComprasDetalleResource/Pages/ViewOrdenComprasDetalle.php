<?php

namespace App\Filament\Resources\OrdenComprasDetalleResource\Pages;

use App\Filament\Resources\OrdenComprasDetalleResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;

class ViewOrdenComprasDetalle extends ViewRecord
{
    protected static string $resource = OrdenComprasDetalleResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->columns(2);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Detalles de la Orden de Compra')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Placeholder::make('tipo_orden')
                        ->label('Tipo de Orden')
                        ->content(fn () => $this->record->ordenCompra->tipoOrdenCompra->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('proveedor')
                        ->label('Proveedor')
                        ->content(fn () => $this->record->ordenCompra->proveedor->nombre_proveedor ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('empresa')
                        ->label('Empresa')
                        ->content(fn () => $this->record->ordenCompra->empresa->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('fecha_realizada')
                        ->label('Fecha Realizada')
                        ->content(function () {
                            $fecha = $this->record->ordenCompra->fecha_realizada;
                            // Si es una cadena, la convertimos a un objeto Carbon
                            if (is_string($fecha)) {
                                $fecha = \Carbon\Carbon::parse($fecha);
                            }
                            // Formateamos solo si $fecha no es nula
                            return $fecha ? $fecha->format('d/m/Y') : 'N/A';
                        })
                        ->extraAttributes(['class' => 'text-gray-600']),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Detalles del Producto')
                ->icon('heroicon-o-archive-box')
                ->schema([
                    Placeholder::make('nombre_producto')
                        ->label('Nombre del Producto')
                        ->content(fn () => $this->record->producto->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-lg font-semibold text-gray-800']),

                    Placeholder::make('sku')
                        ->label('SKU')
                        ->content(fn () => $this->record->producto->sku ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('codigo')
                        ->label('Código de Barras')
                        ->content(fn () => $this->record->producto->codigo ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('unidad_medida')
                        ->label('Unidad de Medida')
                        ->content(fn () => $this->record->producto->unidadDeMedida->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Detalles del Item')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Placeholder::make('cantidad')
                        ->label('Cantidad')
                        ->content(fn () => $this->record->cantidad ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('precio')
                        ->label('Precio Unitario')
                        ->content(fn () => $this->record->precio ? 'HNL ' . number_format($this->record->precio, 2) : 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('total')
                        ->label('Total')
                        ->content(fn () => $this->record->cantidad && $this->record->precio ? 'HNL ' . number_format($this->record->cantidad * $this->record->precio, 2) : 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600 font-semibold']),
                ])
                ->columns(2)
                ->collapsible(),
        ];
    }
}
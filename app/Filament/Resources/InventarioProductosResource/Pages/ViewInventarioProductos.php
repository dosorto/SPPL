<?php

namespace App\Filament\Resources\InventarioProductosResource\Pages;

use App\Filament\Resources\InventarioProductosResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;

class ViewInventarioProductos extends ViewRecord
{
    protected static string $resource = InventarioProductosResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(2)
                    ->schema([
                
                        Section::make('Información del Producto')
                            ->schema([
                                TextEntry::make('producto.nombre')
                                    ->label('Nombre del Producto'),
                                TextEntry::make('producto.sku')
                                    ->label('SKU'),
                                TextEntry::make('producto.unidadDeMedida.nombre')
                                    ->label('Unidad de Medida'),
                                TextEntry::make('producto.descripcion')
                                    ->label('Descripción')
                                    ->columnSpanFull(),
                            ])->columnSpan(1),

                        Section::make('Inventario y Precios')
                            ->schema([
                                TextEntry::make('cantidad')
                                    ->label('Cantidad en Stock')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                TextEntry::make('precio_costo')
                                    ->label('Precio de Costo')
                                    ->money('HNL'),
                                TextEntry::make('precio_detalle')
                                    ->label('Precio de Venta')
                                    ->money('HNL')
                                    ->color('primary'),
                                TextEntry::make('precio_mayorista')
                                    ->label('Precio de Mayorista')
                                    ->money('HNL')
                                    ->color('warning'),
                                TextEntry::make('precio_promocion')
                                    ->label('Precio de Oferta')
                                    ->money('HNL')
                                    ->color('success'),
                            ])->columnSpan(1),
                    ]),
            ]);
    }
}
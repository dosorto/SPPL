<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasDetalleResource\Pages;
use App\Models\OrdenComprasDetalle;
use App\Models\OrdenCompras;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrdenComprasDetalleResource extends Resource
{
    protected static ?string $model = OrdenComprasDetalle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?string $navigationLabel = 'Detalles de Órdenes de Compra';
    protected static ?string $pluralModelLabel = 'Detalles de Órdenes de Compra';
    protected static ?string $modelLabel = 'Detalle de Orden de Compra';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('orden_compra_id')
                ->label('Orden de Compra')
                ->options(function () {
                    return \App\Models\OrdenCompras::with('tipoOrdenCompra')->get()
                        ->mapWithKeys(fn ($ordenCompra) => [
                            $ordenCompra->id => $ordenCompra->tipoOrdenCompra->nombre ?? 'N/A'
                        ]);
                })
                ->searchable()
                ->required(),

            Forms\Components\Select::make('producto_id')
                ->label('Producto')
                ->relationship('producto', 'nombre')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('cantidad')
                ->label('Cantidad')
                ->required()
                ->numeric(),

            Forms\Components\TextInput::make('precio')
                ->label('Precio Unitario')
                ->required()
                ->numeric(),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ordenCompra.tipoOrdenCompra.nombre')
                    ->label('Tipo de Orden')
                    ->sortable(),

                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),

                Tables\Columns\TextColumn::make('precio')
                    ->label('Precio Unitario')
                    ->money('HNL', true)
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Ver'),
                    Tables\Actions\EditAction::make()->label('Editar'),
                    Tables\Actions\DeleteAction::make()->label('Eliminar'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenComprasDetalles::route('/'),
            'create' => Pages\CreateOrdenComprasDetalle::route('/create'),
            'edit' => Pages\EditOrdenComprasDetalle::route('/{record}/edit'),
            'view' => Pages\ViewOrdenComprasDetalle::route('/{record}'),
        ];
    }
}

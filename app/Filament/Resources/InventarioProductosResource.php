<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioProductosResource\Pages;
use App\Filament\Pages\RecibirOrdenCompra;
use App\Models\InventarioProductos;
use App\Models\OrdenCompras;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class InventarioProductosResource extends Resource
{
    protected static ?string $model = InventarioProductos::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Gestión de Inventario';
    protected static ?string $modelLabel = 'Inventario de Producto';
    protected static ?string $navigationGroup = 'Inventario';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Producto')
                    ->schema([
                        Forms\Components\Select::make('producto_id')
                            ->relationship('producto', 'nombre')
                            ->label('Producto')
                            ->disabled(),
                        Forms\Components\TextInput::make('cantidad')
                            ->numeric()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Estructura de Precios')
                    ->schema([
                        Forms\Components\TextInput::make('precio_costo')
                            ->label('Precio de Costo')
                            ->numeric()
                            ->required()
                            ->prefix('HNL'),
                        Forms\Components\TextInput::make('precio_detalle')
                            ->label('Precio de Venta')
                            ->numeric()
                            ->required()
                            ->prefix('HNL'),
                        Forms\Components\TextInput::make('precio_promocion')
                            ->label('Precio de Oferta')
                            ->numeric()
                            ->required()
                            ->prefix('HNL'),
                        
                        Forms\Components\TextInput::make('precio_mayorista')
                            ->label('Precio de Mayorista')
                            ->numeric()
                            ->required()
                            ->prefix('HNL'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cantidad')
                    ->numeric()->sortable(),
                Tables\Columns\TextColumn::make('precio_costo')
                    ->numeric()->sortable()->money('HNL'),
                Tables\Columns\TextColumn::make('precio_detalle')
                    ->numeric()->sortable()->money('HNL'),
                Tables\Columns\TextColumn::make('precio_mayorista')
                    ->numeric()->sortable()->money('HNL'),
                Tables\Columns\TextColumn::make('precio_promocion')
                    ->numeric()->sortable()->money('HNL'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // 
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['producto.unidadDeMedida']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarioProductos::route('/'),
            'view' => Pages\ViewInventarioProductos::route('/{record}'),
            'edit' => Pages\EditInventarioProductos::route('/{record}/edit'),
        ];
    }
}
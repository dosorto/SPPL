<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaClienteProductoResource\Pages;
use App\Filament\Resources\CategoriaClienteProductoResource\RelationManagers;
use App\Models\CategoriaClienteProducto;
use App\Models\CategoriaCliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriaClienteProductoResource extends Resource
{
    protected static ?string $model = CategoriaClienteProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?string $navigationLabel = 'Categorías Cliente-Producto';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('categoria_cliente_id')
                    ->label('Categoría de Cliente')
                    ->options(CategoriaCliente::pluck('nombre', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('categoria_producto_id')
                    ->label('Categoría de Producto')
                    ->options(\App\Models\CategoriaProducto::pluck('nombre', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('descuento_porcentaje')
                    ->label('Descuento (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('categoriaCliente.nombre')
                    ->label('Categoría de Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('categoriaProducto.nombre')
                    ->label('Categoría de Producto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descuento_porcentaje')
                    ->label('Descuento (%)')
                    ->suffix('%'),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaClienteProductos::route('/'),
            'create' => Pages\CreateCategoriaClienteProducto::route('/create'),
            'edit' => Pages\EditCategoriaClienteProducto::route('/{record}/edit'),
            'view' => Pages\ViewCategoriaClienteProducto::route('/{record}'),
        ];
    }
}

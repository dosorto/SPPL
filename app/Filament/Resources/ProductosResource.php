<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductosResource\Pages;
use App\Models\Productos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductosResource extends Resource
{
    protected static ?string $model = Productos::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $navigationGroup = 'Inventario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Datos principales')
                        ->schema([
                            Forms\Components\TextInput::make('nombre')
                                ->label('Nombre del producto')
                                ->required()
                                ->maxLength(100),

                            Forms\Components\Select::make('unidad_de_medida_id')
                                ->label('Unidad de medida')
                                ->relationship('unidadDeMedida', 'nombre')
                                ->searchable()
                                ->required(),

                            Forms\Components\TextInput::make('sku')
                                ->label('SKU')
                                ->maxLength(100),

                            Forms\Components\TextInput::make('codigo')
                                ->label('Código de barras')
                                ->maxLength(100),

                            Forms\Components\TextInput::make('isv')
                                ->label('ISV')
                                ->numeric(),
                        ]),

                    Forms\Components\Wizard\Step::make('Detalles adicionales')
                        ->schema([
                            Forms\Components\Textarea::make('descripcion_corta')
                                ->label('Descripción corta')
                                ->rows(2),

                            Forms\Components\Textarea::make('descripcion')
                                ->label('Descripción larga')
                                ->rows(4),

                            Forms\Components\TextInput::make('color')
                                ->label('Color')
                                ->maxLength(50),
                        ]),

                    Forms\Components\Wizard\Step::make('Imágenes')
                        ->schema([
                            Forms\Components\FileUpload::make('fotos')
                                ->label('Fotos del producto')
                                ->multiple()
                                ->directory('productos')
                                ->image()
                                ->maxSize(2048),
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del producto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unidadDeMedida.nombre')
                    ->label('Unidad de medida')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable(),

                // Código de barras como texto (campo 'codigo' en BD)
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código de barras')
                    ->sortable(),

                // Código de barras SVG generado
                Tables\Columns\ViewColumn::make('codigo')
                    ->label('Código barras (SVG)')
                    ->view('filament.tables.columns.codigo-barra'),

                Tables\Columns\TextColumn::make('isv')
                    ->label('ISV')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Editar'),
                    Tables\Actions\DeleteAction::make()->label('Eliminar'),
                    Tables\Actions\ViewAction::make()->label('Ver'),
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProductos::route('/create'),
            'edit' => Pages\EditProductos::route('/{record}/edit'),
        ];
    }
}

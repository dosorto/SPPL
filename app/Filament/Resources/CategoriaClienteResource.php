<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaClienteResource\Pages;
use App\Filament\Resources\CategoriaClienteResource\RelationManagers;
use App\Models\CategoriaCliente;
use App\Models\CategoriaProducto;
use App\Models\Productos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;

class CategoriaClienteResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('empresa_id', auth()->user()->empresa_id);
    }
    protected static ?string $model = CategoriaCliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Comercial';
    protected static ?string $navigationLabel = 'Categorías de Clientes';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('empresa_id')
                    ->default(fn () => auth()->user()->empresa_id)
                    ->required(),
                Wizard::make([
                    Wizard\Step::make('Información Básica')
                        ->schema([
                            Forms\Components\TextInput::make('nombre')
                                ->label('Nombre de la Categoría')
                                ->required()
                                ->maxLength(100)
                                ->placeholder('Ej: Cliente VIP, Cliente Regular, etc.'),
                            Forms\Components\Textarea::make('descripcion')
                                ->label('Descripción')
                                ->maxLength(255)
                                ->placeholder('Describe brevemente esta categoría de cliente'),
                            Forms\Components\Toggle::make('activo')
                                ->label('Activo')
                                ->default(true)
                                ->helperText('Define si esta categoría está disponible para asignar a clientes'),
                        ]),
                    
                    Wizard\Step::make('Descuentos por Producto')
                        ->schema([
                            Forms\Components\Section::make('Descuentos por Categoría de Producto')
                                ->description('Define los porcentajes de descuento que aplicarán a cada categoría de producto.')
                                ->schema([
                                    Repeater::make('categorias_productos_descuentos')
                                        ->label('Descuentos por Categoría de Producto')
                                        ->schema([
                                            Forms\Components\Select::make('categoria_producto_id')
                                                ->label('Categoría de Producto')
                                                ->options(CategoriaProducto::pluck('nombre', 'id'))
                                                ->required()
                                                ->searchable()
                                                ->distinct()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                            Forms\Components\TextInput::make('descuento_porcentaje')
                                                ->label('Descuento (%)')
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->maxValue(100)
                                                ->step(0.01)
                                                ->suffix('%')
                                                ->required(),
                                            Forms\Components\Toggle::make('activo')
                                                ->label('Activo')
                                                ->default(true),
                                        ])
                                        ->columns(3)
                                        ->defaultItems(0)
                                        ->addActionLabel('Agregar Categoría de Producto')
                                        ->reorderableWithButtons()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => 
                                            !empty($state['categoria_producto_id']) 
                                                ? CategoriaProducto::find($state['categoria_producto_id'])?->nombre . ' - ' . ($state['descuento_porcentaje'] ?? 0) . '%'
                                                : 'Nueva categoría'
                                        ),
                                ])
                                ->collapsible(),

                            Forms\Components\Section::make('Descuentos por Productos Específicos (Opcional)')
                                ->description('Opcionalmente, define descuentos especiales para productos individuales. Estos descuentos tienen prioridad sobre los descuentos por categoría.')
                                ->schema([
                                    Repeater::make('productos_especificos_descuentos')
                                        ->label('Descuentos por Producto Específico')
                                        ->schema([
                                            Forms\Components\Select::make('productos_id')
                                                ->label('Producto')
                                                ->options(function () {
                                                    return Productos::select('id', 'nombre', 'sku')
                                                        ->get()
                                                        ->mapWithKeys(function ($producto) {
                                                            return [$producto->id => $producto->nombre . ' (SKU: ' . $producto->sku . ')'];
                                                        });
                                                })
                                                ->required()
                                                ->searchable()
                                                ->distinct()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                            Forms\Components\TextInput::make('descuento_porcentaje')
                                                ->label('Descuento (%)')
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->maxValue(100)
                                                ->step(0.01)
                                                ->suffix('%')
                                                ->required()
                                                ->helperText('Este descuento tendrá prioridad sobre el descuento por categoría'),
                                            Forms\Components\Toggle::make('activo')
                                                ->label('Activo')
                                                ->default(true),
                                        ])
                                        ->columns(3)
                                        ->defaultItems(0)
                                        ->addActionLabel('Agregar Producto Específico')
                                        ->reorderableWithButtons()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => 
                                            !empty($state['productos_id']) 
                                                ? Productos::find($state['productos_id'])?->nombre . ' - ' . ($state['descuento_porcentaje'] ?? 0) . '%'
                                                : 'Nuevo producto'
                                        ),
                                ])
                                ->collapsible()
                                ->collapsed(),
                        ]),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50),
                Tables\Columns\TextColumn::make('categorias_productos_count')
                    ->label('Categorías Config.')
                    ->counts('categoriasProductos')
                    ->suffix(' categorías'),
                Tables\Columns\TextColumn::make('productos_especificos_count')
                    ->label('Productos Config.')
                    ->counts('productosEspecificos')
                    ->suffix(' productos'),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaClientes::route('/'),
            'create' => Pages\CreateCategoriaCliente::route('/create'),
            'edit' => Pages\EditCategoriaCliente::route('/{record}/edit'),
            'view' => Pages\ViewCategoriaCliente::route('/{record}'),
        ];
    }
}

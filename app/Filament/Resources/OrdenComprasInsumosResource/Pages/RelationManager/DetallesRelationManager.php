<?php

namespace App\Filament\Resources\OrdenComprasInsumosResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\OrdenComprasInsumosDetalle;
use App\Models\TipoOrdenCompras;
use App\Models\Productos;
use App\Models\CategoriaProducto;
use Illuminate\Support\Facades\Log;

class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';
    protected static ?string $recordTitleAttribute = 'producto_id';

    public static function esMateriaPrima($tipoOrdenId): bool
    {
        $tipo = \App\Models\TipoOrdenCompras::find($tipoOrdenId);
        return $tipo?->nombre === 'Materia Prima';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tipo_orden_compra_id')
                    ->label('Tipo de Orden')
                    ->options(
                        TipoOrdenCompras::whereIn('nombre', ['Insumos', 'Materia Prima'])
                            ->pluck('nombre', 'id')
                    )
                    ->required()
                    ->reactive()
                    ->searchable()
                    ->helperText('Seleccione si es Insumos o Materia Prima.'),
                Select::make('producto_id')
                    ->label('Producto')
                    ->options(function ($get) {
                        $tipoOrdenId = $get('tipo_orden_compra_id');
                        $tipo = TipoOrdenCompras::find($tipoOrdenId);
                        $categoriaNombre = $tipo?->nombre ?? null;
                        Log::info('Filtrando productos en RelationManager', ['tipo_orden_compra_id' => $tipoOrdenId, 'categoria_nombre' => $categoriaNombre]);
                        if (!$categoriaNombre) {
                            return ['none' => 'Seleccione un tipo de orden primero'];
                        }
                        $searchTerm = $categoriaNombre === 'Insumos' ? 'insumo' : strtolower($categoriaNombre);
                        $categorias = CategoriaProducto::whereRaw('LOWER(nombre) LIKE ?', ["%$searchTerm%"])->get();
                        if ($categorias->isEmpty()) {
                            Log::warning('Categoría no encontrada', ['categoria_nombre' => $categoriaNombre, 'search_term' => $searchTerm]);
                            return ['none' => 'No hay categoría "' . $categoriaNombre . '" configurada'];
                        }
                        $categoriaIds = $categorias->pluck('id');
                        $productos = Productos::whereIn('categoria_id', $categoriaIds)
                            ->pluck('nombre', 'id')
                            ->toArray();
                        Log::info('Productos encontrados en RelationManager', ['count' => count($productos), 'productos' => $productos, 'categoria_ids' => $categoriaIds->toArray()]);
                        return $productos ?: ['none' => 'No hay productos disponibles para la categoría "' . $categoriaNombre . '"'];
                    })
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->helperText('Seleccione un producto de la categoría correspondiente.'),
                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $precioUnitario = $get('precio_unitario') ?? 0;
                        $set('subtotal', $state * $precioUnitario);
                    })
                    ->helperText('Ingrese la cantidad de productos.'),
                TextInput::make('precio_unitario')
                    ->label('Precio Unitario (HNL)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $cantidad = $get('cantidad') ?? 0;
                        $set('subtotal', $cantidad * $state);
                    })
                    ->helperText('Ingrese el precio por unidad en Lempiras.'),
                TextInput::make('subtotal')
                    ->label('Subtotal (HNL)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(true)
                    ->helperText('Calculado automáticamente.'),
                Section::make('Análisis de Calidad')
                    ->schema([
                        TextInput::make('porcentaje_grasa')
                            ->label('Porcentaje de Grasa (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->helperText('Ejemplo: 3.50'),
                        TextInput::make('porcentaje_proteina')
                            ->label('Porcentaje de Proteína (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->helperText('Ejemplo: 2.80'),
                        TextInput::make('porcentaje_humedad')
                            ->label('Porcentaje de Humedad (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->helperText('Ejemplo: 87.00'),
                        Toggle::make('anomalias')
                            ->label('¿Tiene Anomalías?')
                            ->default(false)
                            ->reactive()
                            ->live()
                            ->helperText('Marque si se detectaron anomalías.'),
                        Textarea::make('detalles_anomalias')
                            ->label('Detalles de Anomalías')
                            ->maxLength(255)
                            ->live()
                            ->visible(fn ($get) => $get('anomalias'))
                            ->helperText('Describa las anomalías detectadas, si las hay.'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->visible(fn ($get) => static::esMateriaPrima($get('tipo_orden_compra_id')))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipoOrdenCompra.nombre')
                    ->label('Tipo de Orden')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_unitario')
                    ->label('Precio Unitario (HNL)')
                    ->money('HNL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal (HNL)')
                    ->money('HNL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('porcentaje_grasa')
                    ->label('Grasa (%)')
                    ->formatStateUsing(fn ($state) => $state ? "$state%" : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => $this->ownerRecord->detalles && $this->ownerRecord->detalles->pluck('tipoOrdenCompra.nombre')->contains('Materia Prima')),
                Tables\Columns\TextColumn::make('porcentaje_proteina')
                    ->label('Proteína (%)')
                    ->formatStateUsing(fn ($state) => $state ? "$state%" : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => $this->ownerRecord->detalles && $this->ownerRecord->detalles->pluck('tipoOrdenCompra.nombre')->contains('Materia Prima')),
                Tables\Columns\TextColumn::make('porcentaje_humedad')
                    ->label('Humedad (%)')
                    ->formatStateUsing(fn ($state) => $state ? "$state%" : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => $this->ownerRecord->detalles && $this->ownerRecord->detalles->pluck('tipoOrdenCompra.nombre')->contains('Materia Prima')),
                Tables\Columns\IconColumn::make('anomalias')
                    ->label('Anomalías')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->visible(fn () => $this->ownerRecord->detalles && $this->ownerRecord->detalles->pluck('tipoOrdenCompra.nombre')->contains('Materia Prima')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Añadir Detalle')
                    ->tooltip('Añadir un nuevo producto a la orden'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->tooltip('Modificar este detalle')
                    ->disabled(fn (OrdenComprasInsumosDetalle $record): bool => $record->ordenComprasInsumos->estado === 'Recibida'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->requiresConfirmation()
                    ->tooltip('Eliminar este detalle')
                    ->disabled(fn (OrdenComprasInsumosDetalle $record): bool => $record->ordenComprasInsumos->estado === 'Recibida'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->requiresConfirmation()
                        ->disabled(fn ($records) => $records && $records->contains(fn (OrdenComprasInsumosDetalle $record): bool => $record->ordenComprasInsumos->estado === 'Recibida')),
                ]),
            ]);
    }
}
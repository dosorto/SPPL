<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasResource\Pages;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\RecibirOrdenCompra;

class OrdenComprasResource extends Resource
{
    protected static ?string $model = OrdenCompras::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?string $navigationLabel = 'Órdenes de Compra';
    protected static ?string $pluralModelLabel = 'Órdenes de Compra';
    protected static ?string $modelLabel = 'Orden de Compra';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Select::make('tipo_orden_compra_id')
                            ->label('Tipo de Orden')
                            ->relationship('tipoOrdenCompra', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(100),
                        Forms\Components\Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedores', 'nombre_proveedor')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(100)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                try {
                                    if (class_exists(\App\Models\Proveedores::class) && $state) {
                                        $proveedor = \App\Models\Proveedores::find($state);
                                        if ($proveedor && $proveedor->empresa_id) {
                                            $set('empresa_id', $proveedor->empresa_id);
                                        } else {
                                            $set('empresa_id', null);
                                        }
                                    }
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body('No se pudo cargar la empresa asociada al proveedor.')
                                        ->danger()
                                        ->send();
                                }
                            }),
                        Forms\Components\Hidden::make('empresa_id')
                            ->required()
                            ->dehydrated(true),
                        Forms\Components\DatePicker::make('fecha_realizada')
                            ->label('Fecha Realizada')
                            ->required()
                            ->default(now()),
                        Forms\Components\Hidden::make('created_by')
                            ->default(Auth::id()),
                        Forms\Components\Hidden::make('updated_by')
                            ->default(Auth::id()),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Section::make('Detalles de la Orden')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Forms\Components\Repeater::make('detalles')
                            ->label('Productos')
                            ->relationship('detalles')
                            ->schema([
                                Forms\Components\Select::make('producto_id')
                                    ->label('Producto')
                                    ->relationship('producto', 'nombre', function ($query) {
                                        $query->where(function ($query) {
                                            $search = request()->input('search');
                                            if ($search) {
                                                $query->where('nombre', 'like', "%{$search}%")
                                                    ->orWhere('barcode', 'like', "%{$search}%")
                                                    ->orWhere('sku', 'like', "%{$search}%");
                                            }
                                        });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->preload()
                                    ->optionsLimit(100)
                                    ->getSearchResultsUsing(function (string $search) {
                                        return \App\Models\Producto::where('nombre', 'like', "%{$search}%")
                                            ->orWhere('barcode', 'like', "%{$search}%")
                                            ->orWhere('sku', 'like', "%{$search}%")
                                            ->limit(100)
                                            ->pluck('nombre', 'id');
                                    })
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        return "{$record->nombre} (Barcode: {$record->barcode}, SKU: {$record->sku})";
                                    }),
                                Forms\Components\TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                                Forms\Components\TextInput::make('precio')
                                    ->label('Precio Unitario')
                                    ->required()
                                    ->numeric()
                                    ->prefix('HNL'),
                                Forms\Components\Hidden::make('created_by')
                                    ->default(Auth::id()),
                                Forms\Components\Hidden::make('updated_by')
                                    ->default(Auth::id()),
                            ])
                            ->columns(3)
                            ->required()
                            ->disabled(function ($get) {
                                return !($get('tipo_orden_compra_id') &&
                                        $get('proveedor_id') &&
                                        $get('empresa_id') &&
                                        $get('fecha_realizada'));
                            }),
                    ])
                    ->collapsible(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipoOrdenCompra.nombre')
                    ->label('Tipo Orden')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('proveedores.nombre_proveedor')
                    ->label('Proveedor')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_realizada')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detalles_count')
                    ->label('Productos')
                    ->counts('detalles')
                    ->sortable(),
            ])
           ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Ver'),
                    Tables\Actions\EditAction::make()->label('Editar'),
                    
                    Action::make('recibirEnInventario')
                        ->label('Recibir en Inventario')
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->color('success')
                        // Oculta el botón si la orden ya fue recibida
                        ->hidden(fn (OrdenCompras $record): bool => $record->estado === 'Recibida')
                        // Genera la URL a la página de recepción, pasando el ID de la orden
                        ->url(fn (OrdenCompras $record): string => RecibirOrdenCompra::getUrl(['orden_id' => $record->id])),
                    

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
        return [
            \App\Filament\Resources\OrdenComprasResource\RelationManagers\DetallesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenCompras::route('/'),
            'create' => Pages\CreateOrdenCompras::route('/create'),
            'edit' => Pages\EditOrdenCompras::route('/{record}/edit'),
            'view' => Pages\ViewOrdenCompras::route('/{record}/detalles'),
        ];
    }
}
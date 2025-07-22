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
                            ->relationship('proveedor', 'nombre_proveedor')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(100)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $proveedor = \App\Models\Proveedores::find($state);
                                $set('empresa_id', $proveedor?->empresa_id ?? null);
                            })
                            ->afterStateHydrated(function ($state, callable $set) {
                                $proveedor = \App\Models\Proveedores::find($state);
                                $set('empresa_id', $proveedor?->empresa_id ?? null);
                            }),
                        Forms\Components\Hidden::make('empresa_id')
                            ->required()
                            ->dehydrated(true),
                        Forms\Components\DatePicker::make('fecha_realizada')
                            ->label('Fecha Realizada')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->nullable()
                            ->maxLength(65535)
                            ->rows(4),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => Auth::id() ?: null),
                        Forms\Components\Hidden::make('updated_by')
                            ->default(fn () => Auth::id() ?: null),
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
                                ->relationship('producto', 'nombre')
                                ->searchable()
                                ->required()
                                ->preload()
                                ->reactive()
                                ->optionsLimit(50)
                                ->getSearchResultsUsing(function (string $search, callable $get) {
                                    $tipoOrdenId = $get('../../tipo_orden_compra_id');

                                    $query = \App\Models\Productos::with(['categoria', 'subcategoria'])
                                        ->where(function ($q) use ($search) {
                                            $q->where('nombre', 'like', "%{$search}%")
                                            ->orWhere('codigo', 'like', "%{$search}%")
                                            ->orWhere('sku', 'like', "%{$search}%");
                                        });

                                    if ($tipoOrdenId) {
                                        $tipoOrden = \App\Models\TipoOrdenCompra::with(['categoria', 'subcategoria'])->find($tipoOrdenId);

                                        if ($tipoOrden?->categoria_id) {
                                            $query->where('categoria_id', $tipoOrden->categoria_id);
                                        }

                                        if ($tipoOrden?->subcategoria_id) {
                                            $query->where('subcategoria_id', $tipoOrden->subcategoria_id);
                                        }
                                    }

                                    if (Auth::check() && !Auth::user()->hasRole('root')) {
                                        $query->where('empresa_id', Auth::user()->empresa_id);
                                    }

                                    return $query->limit(50)->pluck('nombre', 'id');
                                })
                                ->getOptionLabelFromRecordUsing(function ($record) {
                                    return sprintf(
                                        '%s (Categoría: %s, Subcategoría: %s, SKU: %s)',
                                        $record->nombre,
                                        optional($record->categoria)->nombre ?? 'Sin categoría',
                                        optional($record->subcategoria)->nombre ?? 'Sin subcategoría',
                                        $record->sku
                                    );
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
                                    ->default(fn () => Auth::id() ?: null),
                                Forms\Components\Hidden::make('updated_by')
                                    ->default(fn () => Auth::id() ?: null),
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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipoOrdenCompra.nombre')
                    ->label('Tipo Orden')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('proveedor.nombre_proveedor')
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
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'Pendiente' => 'Orden Abierta',
                            'Recibida' => 'Orden en Inventario',
                            default => $state
                        };
                    })
                    ->tooltip(function ($state) {
                        return match ($state) {
                            'Pendiente' => 'La orden ha sido registrada pero aún no se ha recibido en inventario.',
                            'Recibida' => 'La orden de compra ha sido recibida y registrada en el inventario.',
                            default => 'Estado no definido.'
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Ver'),
                    Tables\Actions\EditAction::make()->label('Editar')
                        ->disabled(fn (OrdenCompras $record): bool => $record->estado === 'Recibida'),
                    Action::make('recibirEnInventario')
                        ->label('Recibir en Inventario')
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->color('success')
                        ->hidden(fn (OrdenCompras $record): bool => $record->estado === 'Recibida')
                        ->url(fn (OrdenCompras $record): string => RecibirOrdenCompra::getUrl(['orden_id' => $record->id])),
                    Tables\Actions\DeleteAction::make()->label('Eliminar')
                        ->disabled(fn (OrdenCompras $record): bool => $record->estado === 'Recibida'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados')
                        ->disabled(function ($records) {
                            if (is_null($records) || !$records instanceof \Illuminate\Support\Collection) {
                                return true;
                            }
                            return $records->contains(fn ($record) => $record->estado === 'Recibida');
                        }),
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
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenProduccionResource\Pages;
use App\Filament\Resources\OrdenProduccionResource\RelationManagers;
use App\Models\OrdenProduccion;
use App\Models\Productos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\CategoriaProducto;
use App\Models\UnidadDeMedidas;
use App\Models\InventarioInsumos;


class OrdenProduccionResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('empresa_id', auth()->user()->empresa_id);
    }
    protected static ?string $model = OrdenProduccion::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Órdenes de Producción';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Órdenes de Producción';
    protected static ?string $pluralLabel = 'Órdenes de Producción';
    protected static ?string $label = 'Orden de Producción';
    

    public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Hidden::make('empresa_id')
            ->default(fn () => auth()->user()->empresa_id)
            ->required(),

        // Producto final (sí filtra por empresa)
        Forms\Components\Select::make('producto_id')
            ->label('Producto')
            ->relationship('producto', 'nombre', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('empresa_id', auth()->user()->empresa_id);
            })
            ->searchable()
            ->preload()
            ->required(),

        Forms\Components\Grid::make(2)->schema([
            Forms\Components\TextInput::make('cantidad')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('unidad_de_medida_id')
                ->label('Unidad de Medida')
                ->options(\App\Models\UnidadDeMedidas::pluck('nombre', 'id'))
                ->required(),
        ]),

        Forms\Components\DatePicker::make('fecha_solicitud')->required(),
        Forms\Components\DatePicker::make('fecha_entrega'),

        Forms\Components\Select::make('estado')
            ->options([
                'Pendiente'   => 'Pendiente',
                'En Proceso'  => 'En Proceso',
                'Finalizada'  => 'Finalizada',
                'Cancelada'   => 'Cancelada',
            ])
            ->default('Pendiente')
            ->required(),

        Forms\Components\Textarea::make('observaciones'),

        // ----------- INSUMOS (temporal: sin filtrar por empresa) -----------
        Forms\Components\Repeater::make('insumos')
            ->label('Insumos a utilizar')
            ->relationship('insumos')
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('insumo_id')
                        ->label('Insumo (desde inventario de insumos)')
                        // Opciones iniciales: todos los productos que aparezcan en inventario_insumos (cualquier empresa)
                        ->options(function () {
                            return \App\Models\InventarioInsumos::query()
                                ->with(['producto:id,nombre'])
                                ->get()
                                ->pluck('producto.nombre', 'producto_id') // pluck sobre colección
                                ->toArray();
                        })
                        // Búsqueda: misma fuente (inventario_insumos), sin filtrar por empresa
                        ->getSearchResultsUsing(function (string $search) {
                            $ids = \App\Models\InventarioInsumos::query()
                                ->pluck('producto_id');

                            return \App\Models\Productos::query()
                                ->whereIn('id', $ids)
                                ->where('nombre', 'like', "%{$search}%")
                                ->orderBy('nombre')
                                ->limit(50)
                                ->pluck('nombre', 'id')
                                ->toArray();
                        })
                        // Por si el valor no está en options iniciales
                        ->getOptionLabelUsing(fn ($value) => optional(\App\Models\Productos::find($value))->nombre ?? '')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $insumo = \App\Models\Productos::find($state);
                            $set('unidad_de_medida_id', $insumo?->unidad_de_medida_id);
                        })
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('cantidad_utilizada')
                        ->label('Cantidad utilizada')
                        ->numeric()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('unidad_de_medida_id')
                        ->label('Unidad de Medida')
                        ->options(\App\Models\UnidadDeMedidas::pluck('nombre', 'id'))
                        ->required()
                        ->columnSpan(1),
                ]),
            ])
            ->minItems(1)
            ->required()
            ->columns(1),
    ]);
}


public static function table(Table $table): Table
{
    return $table
        ->defaultSort('id', 'desc')
        ->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable(),

            Tables\Columns\TextColumn::make('producto.nombre')
                ->label('Producto')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('cantidad')
                ->label('Cantidad')
                ->sortable(),

            Tables\Columns\TextColumn::make('unidadDeMedida.nombre')
                ->label('UDM')
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('fecha_solicitud')
                ->label('Fecha solicitud')
                ->date('d/m/Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('fecha_entrega')
                ->label('Fecha entrega')
                ->date('d/m/Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('estado')
                ->label('Estado')
                ->badge()
                ->colors([
                    'warning' => 'Pendiente',
                    'info'    => 'En Proceso',
                    'success' => 'Finalizada',
                    'danger'  => 'Cancelada',
                ])
                ->sortable(),

            Tables\Columns\TextColumn::make('empresa.nombre')
                ->label('Empresa')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

        ])
        ->filters([
            Tables\Filters\SelectFilter::make('estado')->options([
                'Pendiente'   => 'Pendiente',
                'En Proceso'  => 'En Proceso',
                'Finalizada'  => 'Finalizada',
                'Cancelada'   => 'Cancelada',
            ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
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
            // Puedes agregar RelationManagers para insumos si lo deseas
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenProduccions::route('/'),
            'create' => Pages\CreateOrdenProduccion::route('/create'),
            'edit' => Pages\EditOrdenProduccion::route('/{record}/edit'),
            'view' => Pages\ViewOrdenProduccion::route('/{record}'),
        ];
    }
}

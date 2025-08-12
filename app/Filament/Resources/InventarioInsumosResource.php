<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioInsumosResource\Pages;
use App\Models\InventarioInsumos;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class InventarioInsumosResource extends Resource
{
    protected static ?string $model = InventarioInsumos::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Gestión de Inventario Insumos';
    protected static ?string $modelLabel = 'Inventario de Insumos';
    protected static ?string $navigationGroup = 'Órdenes de Producción';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = true;


    public static function canCreate(): bool
    {
        return false; // No manual creation allowed
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Insumo')
                    ->icon('heroicon-o-information-circle')
                    ->description('Detalles del insumo en inventario.')
                    ->schema([
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nombre')
                            ->searchable()
                            ->required()
                            ->hidden()
                            ->default(fn () => Filament::auth()->user()?->empresa_id)
                            ->disabled()
                            ->dehydrated(true),
                        Forms\Components\Select::make('producto_id')
                            ->relationship('producto', 'nombre')
                            ->label('Insumo')
                            ->disabled()
                            ->helperText('El insumo se asigna automáticamente desde la orden de compra.'),
                        Forms\Components\TextInput::make('cantidad')
                            ->numeric()
                            ->required()
                            ->label('Cantidad Disponible')
                            ->minValue(0)
                            ->helperText('Cantidad actual en inventario.'),
                    ])->columns(2),
                Forms\Components\Section::make('Costo')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('precio_costo')
                            ->label('Precio de Costo')
                            ->numeric()
                            ->required()
                            ->prefix('HNL')
                            ->minValue(0)
                            ->helperText('Precio por unidad en Lempiras.'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('producto.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->tooltip('Código único del producto.'),
                TextColumn::make('producto.nombre')
                    ->label('Insumo')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->producto->descripcion_corta ?? '')
                    ->tooltip('Nombre del insumo.'),
                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cantidad')
                    ->numeric()
                    ->sortable()
                    ->label('Cantidad')
                    ->badge()
                    ->color(fn ($state) => $state <= 10 ? 'danger' : ($state <= 50 ? 'warning' : 'success'))
                    ->tooltip(fn ($state) => $state <= 10 ? 'Stock crítico' : ($state <= 50 ? 'Stock bajo' : 'Stock suficiente')),
                TextColumn::make('precio_costo')
                    ->numeric()
                    ->sortable()
                    ->money('HNL')
                    ->label('Precio de Costo')
                    ->tooltip('Precio unitario en Lempiras.'),
                TextColumn::make('total_valor')
                    ->label('Valor Total')
                    ->getStateUsing(fn ($record) => $record->cantidad * $record->precio_costo)
                    ->money('HNL')
                    ->tooltip('Valor total del inventario (Cantidad x Precio de Costo).'),
            ])
            ->filters([
                Filter::make('stock_bajo')
                    ->label('Stock Bajo')
                    ->query(fn (Builder $query) => $query->where('cantidad', '<=', 50))
                    ->toggle(),
                Filter::make('sin_stock')
                    ->label('Sin Stock')
                    ->query(fn (Builder $query) => $query->where('cantidad', '=', 0))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('resumen_inventario')
                    ->label('Resumen de Inventario')
                    ->icon('heroicon-o-chart-bar')
                    ->action(function () {
                        $totalItems = InventarioInsumos::count();
                        $totalCantidad = InventarioInsumos::sum('cantidad');
                        $totalValor = InventarioInsumos::sum(\Illuminate\Support\Facades\DB::raw('cantidad * precio_costo'));
                        Notification::make()
                            ->title('Resumen de Inventario')
                            ->body("Total de insumos: {$totalItems}\nCantidad total: {$totalCantidad}\nValor total: HNL " . number_format($totalValor, 2))
                            ->success()
                            ->send();
                    })
                    ->color('info'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Editar Insumo')
                        ->modalDescription('Confirme los cambios en el inventario.'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Insumo')
                        ->modalDescription('¿Está seguro de eliminar este registro de inventario?'),
                ])
                    ->label('Acciones')
                    ->button()
                    ->outlined()
                    ->dropdown(true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Registros')
                        ->modalDescription('¿Está seguro de eliminar los registros seleccionados?'),
                ]),
            ])
            ->defaultSort('cantidad', 'desc')
            ->paginated([10, 25, 50])
            ->recordClasses(fn ($record) => $record->cantidad <= 10 ? 'bg-red-50' : null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarioInsumos::route('/'),
            'edit' => Pages\EditInventarioInsumos::route('/{record}/edit'),
        ];
    }
}
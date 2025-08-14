<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RendimientoResource\Pages;
use App\Models\Rendimiento;
use App\Models\OrdenProduccion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use App\Models\InventarioProductos;
use App\Models\MovimientoInventario;
use Illuminate\Database\Eloquent\Builder;

class RendimientoResource extends Resource
{
    protected static ?string $model = Rendimiento::class;
    

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('orden_produccion_id')
                ->label('Orden de Producción')
                ->relationship('ordenProduccion', 'id')
                ->getOptionLabelFromRecordUsing(fn ($record) => 'ID: ' . $record->id . ' - ' . ($record->producto->nombre ?? ''))
                ->required(),
            Forms\Components\Repeater::make('productos_finales')
                ->label('Productos Finales')
                ->relationship('productosFinales')
                ->schema([
                    Forms\Components\Select::make('producto_id')
                        ->label('Producto')
                        ->relationship('producto', 'nombre')
                        ->required(),
                    Forms\Components\TextInput::make('cantidad')
                        ->label('Cantidad')
                        ->numeric()
                        ->minValue(0.01)
                        ->required(),
                    Forms\Components\Select::make('unidad_de_medida_id')
                        ->label('Unidad de Medida')
                        ->relationship('unidadDeMedida', 'nombre')
                        ->required(),
                ])
                ->minItems(1)
                ->columns(3),
            Forms\Components\TextInput::make('cantidad_mp')
                ->label('Cantidad MP')
                ->numeric()
                ->minValue(0.01)
                ->required(),
            Forms\Components\TextInput::make('precio_mp')
                ->label('Precio MP')
                ->numeric()
                ->minValue(0)
                ->required(),
            Forms\Components\TextInput::make('precio_otros_mp')
                ->label('Precio Otros MP')
                ->numeric()
                ->minValue(0)
                ->required(),
            Forms\Components\TextInput::make('margen_ganancia')
                ->label('Margen de Ganancia (%)')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->required()
                ->helperText('Se calcula automáticamente: ((Precio MP - Precio Otros MP) / Precio MP) * 100')
                ->reactive()
                ->afterStateHydrated(function ($state, callable $set, $get) {
                    $precioMp = floatval($get('precio_mp'));
                    $precioOtros = floatval($get('precio_otros_mp'));
                    if ($precioMp > 0) {
                        $margen = (($precioMp - $precioOtros) / $precioMp) * 100;
                        $set('margen_ganancia', round($margen, 2));
                    }
                })
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    $precioMp = floatval($get('precio_mp'));
                    $precioOtros = floatval($get('precio_otros_mp'));
                    if ($precioMp > 0) {
                        $margen = (($precioMp - $precioOtros) / $precioMp) * 100;
                        $set('margen_ganancia', round($margen, 2));
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Error: El precio MP debe ser mayor que 0 para calcular el margen de ganancia')
                            ->danger()
                            ->send();
                    }
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('ordenes_produccion.id')->label('Orden de Producción'),
            Tables\Columns\TextColumn::make('productosFinales')
                ->label('Productos Finales')
                ->formatStateUsing(function ($productos) {
                    if (!$productos || count($productos) === 0) return '-';
                    return collect($productos)->map(function ($pf) {
                        $nombre = $pf->producto->nombre ?? '';
                        $cantidad = $pf->cantidad;
                        $unidad = $pf->unidadDeMedida->nombre ?? '';
                        return "$nombre ($cantidad $unidad)";
                    })->implode(', ');
                }),
            Tables\Columns\TextColumn::make('cantidad_mp')->label('Cantidad MP'),
            Tables\Columns\TextColumn::make('precio_mp')->label('Precio MP'),
            Tables\Columns\TextColumn::make('precio_otros_mp')->label('Precio Otros MP'),
            Tables\Columns\TextColumn::make('margen_ganancia')->label('Margen de Ganancia'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Creado'),
            Tables\Columns\TextColumn::make('enviado_a_inventario_at')->dateTime()->label('Enviado a Inventario'),
            Tables\Columns\TextColumn::make('enviado_a_inventario_por')->label('Usuario Envío')->formatStateUsing(fn($id) => optional(\App\Models\User::find($id))->name ?? ($id ? 'ID '.$id : '-')),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
            Action::make('enviar_a_inventario')
                ->label('Enviar a Inventario')
                ->icon('heroicon-o-arrow-up-tray')
                ->requiresConfirmation()
                ->visible(fn ($record) => auth()->user()?->can('rendimientos_actualizar') && !$record->enviado_a_inventario_at)
                ->action(function ($record) {
                    if ($record->enviado_a_inventario_at) {
                        \Filament\Notifications\Notification::make()
                            ->title('Ya fue enviado a inventario')
                            ->danger()
                            ->send();
                        return;
                    }
                    $empresaId = $record->ordenProduccion->empresa_id;
                    $userId = auth()->id() ?? null;
                    $productos = $record->productosFinales;
                    foreach ($productos as $pf) {
                        $productoId = $pf->producto_id;
                        $cantidad = $pf->cantidad;
                        $inventario = \App\Models\InventarioProductos::where('producto_id', $productoId)
                            ->where('empresa_id', $empresaId)
                            ->first();
                        if ($inventario) {
                            $inventario->cantidad += $cantidad;
                            $inventario->save();
                        } else {
                            \App\Models\InventarioProductos::create([
                                'producto_id' => $productoId,
                                'cantidad' => $cantidad,
                                'empresa_id' => $empresaId,
                                'precio_costo' => 0,
                                'precio_detalle' => 0,
                                'precio_promocion' => 0,
                                'precio_mayorista' => 0,
                                'created_by' => $userId,
                            ]);
                        }
                        \App\Models\MovimientoInventario::create([
                            'empresa_id' => $empresaId,
                            'producto_id' => $productoId,
                            'tipo' => 'entrada',
                            'cantidad' => $cantidad,
                            'motivo' => 'Producción finalizada (manual)',
                            'usuario_id' => $userId,
                            'referencia' => 'rendimiento:' . $record->id,
                        ]);
                    }
                    $record->enviado_a_inventario_at = now();
                    $record->enviado_a_inventario_por = $userId;
                    $record->save();
                    \Filament\Notifications\Notification::make()
                        ->title('Productos enviados a inventario')
                        ->success()
                        ->send();
                }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRendimientos::route('/'),
            'create' => Pages\CreateRendimiento::route('/create'),
            'edit' => Pages\EditRendimiento::route('/{record}/edit'),
        ];
    }
}

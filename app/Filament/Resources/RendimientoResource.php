<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RendimientoResource\Pages;
use App\Models\Rendimiento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class RendimientoResource extends Resource
{
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['productosFinales.producto', 'productosFinales.unidadMedida'])
            ->orderByDesc('created_at');
    }
    protected static ?string $model = Rendimiento::class;
    protected static ?string $navigationGroup = 'Órdenes de Producción';
    protected static ?string $navigationLabel = 'Rendimientos';
    protected static ?string $pluralLabel = 'Rendimientos';
    protected static ?string $label = 'Rendimiento';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Select::make('orden_produccion_id')
            ->label('Orden de Producción')
            ->relationship('ordenProduccion', 'id')
            ->getOptionLabelFromRecordUsing(fn ($record) => 'ID: ' . $record->id . ' - ' . ($record->producto->nombre ?? ''))
            ->required()
            ->reactive()
            ->afterStateUpdated(function ($state, callable $set, $get) {
                // Validar si ya existe un rendimiento para esta orden
                $existe = \App\Models\Rendimiento::where('orden_produccion_id', $state)->exists();
                if ($existe) {
                    \Filament\Notifications\Notification::make()
                        ->title('Ya existe un rendimiento para esta orden de producción')
                        ->danger()
                        ->send();
                    $set('orden_produccion_id', null);
                }
                // Forzar el recalculo de los campos dependientes
                $set('costo_insumos', null);
                $set('subtotal', null);
                $set('rendimiento', null);
                $set('precio_venta', null);
            }),

        Forms\Components\TextInput::make('costo_insumos')
            ->label('Costo Insumos')
            ->numeric()
            ->readOnly()
            ->reactive()
            ->afterStateHydrated(function ($state, callable $set, $get) {
                $ordenId = $get('orden_produccion_id');
                $empresaId = optional(\App\Models\OrdenProduccion::find($ordenId))->empresa_id;
                $total = 0;
                if ($ordenId && $empresaId) {
                    $orden = \App\Models\OrdenProduccion::find($ordenId);
                    if ($orden && $orden->insumos) {
                        foreach ($orden->insumos as $insumo) {
                            $inventario = \App\Models\InventarioInsumos::where('producto_id', $insumo->insumo_id)
                                ->where('empresa_id', $empresaId)
                                ->first();
                            $precioUnitario = $inventario?->precio_costo ?? 0;
                            $total += $insumo->cantidad_utilizada * $precioUnitario;
                        }
                    }
                }
                $set('costo_insumos', round($total, 2));
            })
            ->afterStateUpdated(function ($state, callable $set, $get) {
                // recalcula subtotal
                $set('subtotal', floatval($state));
                $set('precio_venta', null);
            }),

        Forms\Components\TextInput::make('subtotal')
            ->label('Subtotal')
            ->numeric()
            ->readOnly()
            ->reactive()
            ->afterStateHydrated(function ($state, callable $set, $get) {
                $set('subtotal', floatval($get('costo_insumos')));
            })
            ->afterStateUpdated(function ($state, callable $set, $get) {
                $set('precio_venta', null);
            }),

        Forms\Components\TextInput::make('rendimiento')
            ->label('Rendimiento')
            ->numeric()
            ->readOnly()
            ->helperText('Ejemplo: cantidad_producida / cantidad_insumo_clave')
            ->reactive()
            ->afterStateHydrated(function ($state, callable $set, $get) {
                $ordenId = $get('orden_produccion_id');
                $orden = \App\Models\OrdenProduccion::find($ordenId);
                $cantidadProducida = 0;
                $cantidadInsumoClave = 0;
                $productosFinales = $get('productos_finales') ?? [];
                if (count($productosFinales)) {
                    $cantidadProducida = $productosFinales[0]['cantidad'] ?? 0;
                }
                if ($orden && $orden->insumos && count($orden->insumos)) {
                    $cantidadInsumoClave = $orden->insumos[0]->cantidad_utilizada ?? 0;
                }
                $rendimiento = $cantidadInsumoClave > 0 ? $cantidadProducida / $cantidadInsumoClave : 0;
                $set('rendimiento', round($rendimiento, 2));
            }),

        Forms\Components\TextInput::make('precio_venta')
            ->label('Precio Venta')
            ->numeric()
            ->readOnly()
            ->helperText('subtotal + (subtotal * margen_ganancia / 100)')
            ->reactive()
            ->afterStateHydrated(function ($state, callable $set, $get) {
                $subtotal = floatval($get('subtotal'));
                $margen = floatval($get('margen_ganancia'));
                $precioVenta = $subtotal + ($subtotal * $margen / 100);
                $set('precio_venta', round($precioVenta, 2));
            })
            ->afterStateUpdated(function ($state, callable $set, $get) {
                $subtotal = floatval($get('subtotal'));
                $margen = floatval($get('margen_ganancia'));
                $precioVenta = $subtotal + ($subtotal * $margen / 100);
                $set('precio_venta', round($precioVenta, 2));
            }),

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
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        // recalcula rendimiento
                        $set('rendimiento', null);
                    }),
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
            ->required()
            ->reactive()
            ->afterStateUpdated(function ($state, callable $set, $get) {
                // recalcula margen
                $precioOtros = floatval($get('precio_otros_mp'));
                if (floatval($state) > 0) {
                    $margen = ((floatval($state) - $precioOtros) / floatval($state)) * 100;
                    $set('margen_ganancia', round($margen, 2));
                } else {
                    $set('margen_ganancia', 0);
                }
            }),

        Forms\Components\TextInput::make('precio_otros_mp')
            ->label('Precio Otros MP')
            ->numeric()
            ->minValue(0)
            ->required()
            ->reactive()
            ->afterStateUpdated(function ($state, callable $set, $get) {
                $precioMp = floatval($get('precio_mp'));
                if ($precioMp > 0) {
                    $margen = (($precioMp - floatval($state)) / $precioMp) * 100;
                    $set('margen_ganancia', round($margen, 2));
                } else {
                    $set('margen_ganancia', 0);
                }
            }),

        Forms\Components\TextInput::make('margen_ganancia')
            ->label('Margen de Ganancia (%)')
            ->numeric()
            ->minValue(0)
            ->maxValue(100)
            ->required()
            ->helperText('Se calcula automáticamente: ((Precio MP - Precio Otros MP) / Precio MP) * 100')
            ->readOnly()
            ->reactive()
            ->afterStateHydrated(function ($state, callable $set, $get) {
                $precioMp = floatval($get('precio_mp'));
                $precioOtros = floatval($get('precio_otros_mp'));
                if ($precioMp > 0) {
                    $margen = (($precioMp - $precioOtros) / $precioMp) * 100;
                    $set('margen_ganancia', round($margen, 2));
                }
            }),
    ]);
}

    public static function table(Table $table): Table
    {
    return $table->columns([
            Tables\Columns\TextColumn::make('ordenProduccion.producto.nombre')->label('Producto'),
            Tables\Columns\TextColumn::make('productosFinales')
                ->label('Productos Finales')
                ->formatStateUsing(function ($state) {
                    if (!is_iterable($state)) {
                        return '-';
                    }
                    $items = collect($state)->map(function ($pf) {
                        $nombre = $pf->producto->nombre ?? '';
                        $cantidad = $pf->cantidad ?? '';
                        $unidad = $pf->unidadMedida ? $pf->unidadMedida->nombre : '';
                        return $nombre ? "$nombre ($cantidad $unidad)" : '';
                    })->filter();
                    return $items->isEmpty() ? '-' : $items->implode(', ');
                }),
            Tables\Columns\TextColumn::make('cantidad_mp')->label('Cantidad Materia Prima'),
            Tables\Columns\TextColumn::make('precio_mp')->label('Precio Materia Prima'),
            Tables\Columns\TextColumn::make('precio_otros_mp')->label('Precio Otros Insumos'),
            Tables\Columns\TextColumn::make('margen_ganancia')->label('Margen de Ganancia'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Creado'),
            Tables\Columns\TextColumn::make('enviado_a_inventario_at')->dateTime()->label('Enviado a Inventario'),
            Tables\Columns\TextColumn::make('enviado_a_inventario_por')->label('Usuario Envío')->formatStateUsing(function ($state) {
                if (!$state) return '-';
                $user = \App\Models\User::find($state);
                return $user ? $user->name : 'ID '.$state;
            }),
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
                        if (!$productoId) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error: producto_id nulo')
                                ->body('No se puede crear inventario para un producto sin ID.')
                                ->danger()
                                ->send();
                            continue;
                        }
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
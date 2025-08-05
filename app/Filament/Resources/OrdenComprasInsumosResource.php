<?php 

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasInsumosResource\Pages;
use App\Models\OrdenComprasInsumos;
use App\Models\TipoOrdenCompras;
use App\Models\Proveedores;
use App\Models\Empresa;
use App\Models\Productos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\RecibirOrdenCompraInsumos;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenComprasInsumosResource extends Resource
{
    protected static ?string $model = OrdenComprasInsumos::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Insumos y Materia Prima';
    protected static ?string $navigationLabel = 'Órdenes de Compra Insumos';
    protected static ?string $pluralModelLabel = 'Órdenes de Compra Insumos';
    protected static ?string $modelLabel = 'Orden de Compra Insumos';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo_orden_compra_id')
                    ->label('Tipo de Orden')
                    ->options(
                        \App\Models\TipoOrdenCompras::whereIn('nombre', ['Insumos', 'Materia Prima'])
                            ->pluck('nombre', 'id')
                    )
                    ->required()
                    ->reactive()
                    ->searchable(),

                Forms\Components\Select::make('proveedor_id')
                    ->label('Proveedor')
                    ->options(\App\Models\Proveedores::pluck('nombre_proveedor', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->options(\App\Models\Empresa::pluck('nombre', 'id'))
                    ->required()
                    ->default(function () {
                        $empresaId = Auth::user()->empresa_id;
                        if (!$empresaId) {
                            \Log::warning('Usuario sin empresa_id asignado', ['user_id' => Auth::user()->id]);
                            $empresaId = \App\Models\Empresa::first()->id ?? null;
                        }
                        return $empresaId;
                    })
                    ->disabled(fn () => !Auth::user()->hasRole('Super Admin'))
                    ->dehydrated(true),

                Forms\Components\DatePicker::make('fecha_realizada')
                    ->label('Fecha')
                    ->required()
                    ->default(now()),

                // 📦 Detalles de la Orden (AHORA ANTES DEL ANÁLISIS)
                Forms\Components\Repeater::make('detalles')
                    ->label('Detalles de la Orden')
                    ->relationship('detalles')
                    ->schema([
                        Forms\Components\Select::make('producto_id')
                            ->label('Producto')
                            ->options(function () {
                                return \App\Models\Productos::whereHas('categoria', function ($query) {
                                    $query->whereIn('nombre', ['Insumos', 'Materia Prima']);
                                })->pluck('nombre', 'id');
                            })
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $precioUnitario = $get('precio_unitario') ?? 0;
                                $set('subtotal', $state * $precioUnitario);
                            }),

                        Forms\Components\TextInput::make('precio_unitario')
                            ->label('Precio Unitario (HNL)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $cantidad = $get('cantidad') ?? 0;
                                $set('subtotal', $cantidad * $state);
                            }),

                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (HNL)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true),
                    ])
                    ->columns(4)
                    ->itemLabel(fn (array $state): ?string => \App\Models\Productos::find($state['producto_id'])?->nombre ?? null)
                    ->minItems(1)
                    ->required(fn ($get) => static::esMateriaPrima($get('tipo_orden_compra_id'))),

                // 🧪 Análisis de Calidad
                Forms\Components\Section::make('Análisis de Calidad')
                    ->schema([
                        Forms\Components\TextInput::make('porcentaje_grasa')
                            ->label('Porcentaje de Grasa (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->visible(fn ($get) => static::esMateriaPrima($get('tipo_orden_compra_id'))),

                        Forms\Components\TextInput::make('porcentaje_proteina')
                            ->label('Porcentaje de Proteína (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->visible(fn ($get) => static::esMateriaPrima($get('tipo_orden_compra_id'))),

                        Forms\Components\TextInput::make('porcentaje_humedad')
                            ->label('Porcentaje de Humedad (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->visible(fn ($get) => static::esMateriaPrima($get('tipo_orden_compra_id'))),

                        Forms\Components\Toggle::make('anomalias')
                            ->label('¿Tiene Anomalías?')
                            ->default(false)
                            ->visible(fn ($get) => static::esMateriaPrima($get('tipo_orden_compra_id'))),

                        Forms\Components\Textarea::make('detalles_anomalias')
                            ->label('Detalles de Anomalías')
                            ->maxLength(255)
                            ->visible(fn ($get) => static::esMateriaPrima($get('tipo_orden_compra_id')) && $get('anomalias')),
                    ])
                    ->collapsible(),
            ]);
    }

    // Función auxiliar para identificar tipo de orden
    private static function esMateriaPrima($tipoOrdenId): bool
    {
        $tipo = \App\Models\TipoOrdenCompras::find($tipoOrdenId);
        return $tipo?->nombre === 'Materia Prima';
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
                Tables\Columns\IconColumn::make('anomalias')
                    ->label('Anomalías')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Ver'),
                    Tables\Actions\EditAction::make()->label('Editar')
                        ->disabled(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida'),
                    Tables\Actions\Action::make('recibirEnInventario')
                        ->label('Recibir en Inventario')
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->color('success')
                        ->hidden(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida')
                        ->url(function (OrdenComprasInsumos $record) {
                            $url = \App\Filament\Pages\RecibirOrdenCompraInsumos::getUrl(['orden_id' => $record->id]);
                            \Illuminate\Support\Facades\Log::info('URL generada para recibirEnInventario: ' . $url);
                            return $url;
                        }),
                    Tables\Actions\Action::make('generatePdf')
                        ->label('Generar PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->hidden(fn (OrdenComprasInsumos $record): bool => $record->estado !== 'Recibida')
                        ->action(function (OrdenComprasInsumos $record) {
                            $pdf = Pdf::loadView('pdf.orden-compra-insumos', [
                                'orden' => $record->load(['empresa', 'proveedor', 'tipoOrdenCompra', 'detalles.producto']),
                                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                            ]);
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, "orden-compra-insumos-{$record->id}.pdf");
                        }),
                    Tables\Actions\DeleteAction::make()->label('Eliminar')
                        ->disabled(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenComprasInsumos::route('/'),
            'create' => Pages\CreateOrdenComprasInsumos::route('/create'),
            'edit' => Pages\EditOrdenComprasInsumos::route('/{record}/edit'),
            'view' => Pages\ViewOrdenComprasInsumos::route('/{record}/detalles'),
        ];
    }
} 


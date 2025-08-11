<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasInsumosResource\Pages;
use App\Filament\Resources\OrdenComprasInsumosResource\RelationManagers\DetallesRelationManager;
use App\Models\OrdenComprasInsumos;
use App\Models\TipoOrdenCompras;
use App\Models\Proveedores;
use App\Models\Empresa;
use App\Models\Productos;
use App\Models\CategoriaProducto;
use App\Models\Paises;
use App\Models\Departamento;
use App\Models\Municipio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Filament\Pages\RecibirOrdenCompraInsumos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;

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
                Forms\Components\Section::make('Datos de la Orden')
                    ->icon('heroicon-o-rectangle-stack')
                    ->description('Ingrese los detalles de la orden y añada productos. Los detalles se gestionarán en una tabla al editar.')
                    ->schema([
                        Forms\Components\Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->options(\App\Models\Proveedores::pluck('nombre_proveedor', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(100)
                            ->placeholder('Seleccione un proveedor o presione "+"')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('createProveedor')
                                    ->label('Agregar')
                                    ->icon('heroicon-o-plus')
                                    ->tooltip('Agregar un nuevo proveedor')
                                    ->modalHeading('Crear Nuevo Proveedor')
                                    ->form([
                                        Forms\Components\TextInput::make('nombre_proveedor')
                                            ->label('Nombre del Proveedor')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('telefono')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->required()
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('rtn')
                                            ->label('RTN')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('persona_contacto')
                                            ->label('Persona de Contacto')
                                            ->maxLength(255),
                                        Forms\Components\Select::make('empresa_id')
                                            ->label('Empresa')
                                            ->relationship('empresa', 'nombre')
                                            ->searchable()
                                            ->required()
                                            ->default(fn () => Filament::auth()->user()?->empresa_id)
                                            ->disabled()
                                            ->dehydrated(true),
                                        Forms\Components\Select::make('pais_id')
                                            ->label('País')
                                            ->searchable()
                                            ->options(Paises::pluck('nombre_pais', 'id'))
                                            ->placeholder('Seleccione un país')
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set) => [
                                                $set('departamento_id', null),
                                                $set('municipio_id', null),
                                            ]),
                                        Forms\Components\Select::make('departamento_id')
                                            ->label('Departamento')
                                            ->searchable()
                                            ->placeholder('Seleccione un Departamento')
                                            ->options(fn (callable $get) =>
                                                Departamento::where('pais_id', $get('pais_id'))
                                                    ->pluck('nombre_departamento', 'id')
                                            )
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set) => $set('municipio_id', null))
                                            ->disabled(fn (callable $get) => !$get('pais_id')),
                                        Forms\Components\Select::make('municipio_id')
                                            ->label('Municipio')
                                            ->searchable()
                                            ->placeholder('Seleccione un Municipio')
                                            ->options(fn (callable $get) =>
                                                Municipio::where('departamento_id', $get('departamento_id'))
                                                    ->pluck('nombre_municipio', 'id')
                                            )
                                            ->required()
                                            ->disabled(fn (callable $get) => !$get('departamento_id')),
                                        Forms\Components\Textarea::make('direccion')
                                            ->label('Dirección')
                                            ->columnSpanFull(),
                                    ])
                                    ->action(function (array $data, Forms\Components\Actions\Action $action, callable $set) {
                                        $newProveedor = Proveedores::create($data);
                                        Notification::make()
                                            ->title('Proveedor Creado')
                                            ->body("El proveedor {$newProveedor->nombre_proveedor} ha sido creado con éxito.")
                                            ->success()
                                            ->send();
                                        $set('proveedor_id', $newProveedor->id);
                                        $set('empresa_id', $newProveedor->empresa_id);
                                    })
                                    ->slideOver()
                            )
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $proveedor = \App\Models\Proveedores::find($state);
                                $empresaId = $proveedor?->empresa_id ?? (Auth::user()->empresa_id ?? \App\Models\Empresa::first()->id);
                                $set('empresa_id', $empresaId);
                            })
                            ->columnSpan(1)
                            ->helperText('Seleccione el proveedor de la orden o cree uno nuevo.'),
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->options(\App\Models\Empresa::pluck('nombre', 'id'))
                            ->required()
                            ->default(function () {
                                $empresaId = Auth::user()->empresa_id;
                                if (!$empresaId) {
                                    Log::warning('Usuario sin empresa_id asignado', ['user_id' => Auth::user()->id]);
                                    $empresaId = \App\Models\Empresa::first()->id ?? null;
                                }
                                return $empresaId;
                            })
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpan(1)
                            ->helperText('La empresa se asigna automáticamente según el proveedor.'),
                        Forms\Components\DatePicker::make('fecha_realizada')
                            ->label('Fecha')
                            ->required()
                            ->default(now())
                            ->columnSpan(1)
                            ->helperText('Seleccione la fecha de la orden.'),
                        Forms\Components\Repeater::make('detalles')
                            ->label('Detalles de la Orden')
                            ->relationship('detalles')
                            ->schema([
                                Forms\Components\Select::make('tipo_orden_compra_id')
                                    ->label('Tipo de Orden')
                                    ->options(
                                        \App\Models\TipoOrdenCompras::whereIn('nombre', ['Insumos', 'Materia Prima'])
                                            ->pluck('nombre', 'id')
                                    )
                                    ->required()
                                    ->reactive()
                                    ->searchable()
                                    ->columnSpan(1)
                                    ->helperText('Seleccione si es Insumos o Materia Prima.'),
                                Forms\Components\Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(function ($get) {
                                        $tipoOrdenId = $get('tipo_orden_compra_id');
                                        $tipo = \App\Models\TipoOrdenCompras::find($tipoOrdenId);
                                        $categoriaNombre = $tipo?->nombre ?? null;
                                        Log::info('Filtrando productos', ['tipo_orden_compra_id' => $tipoOrdenId, 'categoria_nombre' => $categoriaNombre]);
                                        if (!$categoriaNombre) {
                                            return ['none' => 'Seleccione un tipo de orden primero'];
                                        }
                                        $searchTerm = $categoriaNombre === 'Insumos' ? 'insumo' : strtolower($categoriaNombre);
                                        $categorias = \App\Models\CategoriaProducto::whereRaw('LOWER(nombre) LIKE ?', ["%$searchTerm%"])->get();
                                        if ($categorias->isEmpty()) {
                                            Log::warning('Categoría no encontrada', ['categoria_nombre' => $categoriaNombre, 'search_term' => $searchTerm]);
                                            return ['none' => 'No hay categoría "' . $categoriaNombre . '" configurada'];
                                        }
                                        $categoriaIds = $categorias->pluck('id');
                                        $productos = \App\Models\Productos::whereIn('categoria_id', $categoriaIds)
                                            ->pluck('nombre', 'id')
                                            ->toArray();
                                        Log::info('Productos encontrados', ['count' => count($productos), 'productos' => $productos, 'categoria_ids' => $categoriaIds->toArray()]);
                                        return $productos ?: ['none' => 'No hay productos disponibles para la categoría "' . $categoriaNombre . '"'];
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->columnSpan(1)
                                    ->helperText('Seleccione un producto de la categoría correspondiente.'),
                                Forms\Components\TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $precioUnitario = $get('precio_unitario') ?? 0;
                                        $set('subtotal', $state * $precioUnitario);
                                    })
                                    ->columnSpan(1)
                                    ->helperText('Ingrese la cantidad de productos.'),
                                Forms\Components\TextInput::make('precio_unitario')
                                    ->label('Precio Unitario (HNL)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $cantidad = $get('cantidad') ?? 0;
                                        $set('subtotal', $cantidad * $state);
                                    })
                                    ->columnSpan(1)
                                    ->helperText('Ingrese el precio por unidad en Lempiras.'),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal (HNL)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(1)
                                    ->helperText('Calculado automáticamente.'),
                                Forms\Components\Section::make('Análisis de Calidad')
                                    ->schema([
                                        Forms\Components\TextInput::make('porcentaje_grasa')
                                            ->label('Porcentaje de Grasa (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->required()
                                            ->helperText('Ejemplo: 3.50'),
                                        Forms\Components\TextInput::make('porcentaje_proteina')
                                            ->label('Porcentaje de Proteína (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->required()
                                            ->helperText('Ejemplo: 2.80'),
                                        Forms\Components\TextInput::make('porcentaje_humedad')
                                            ->label('Porcentaje de Humedad (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->required()
                                            ->helperText('Ejemplo: 87.00'),
                                        Forms\Components\Toggle::make('anomalias')
                                            ->label('¿Tiene Anomalías?')
                                            ->default(false)
                                            ->reactive()
                                            ->live()
                                            ->helperText('Marque si se detectaron anomalías.'),
                                        Forms\Components\Textarea::make('detalles_anomalias')
                                            ->label('Detalles de Anomalías')
                                            ->maxLength(255)
                                            ->live()
                                            ->visible(fn ($get) => $get('anomalias'))
                                            ->helperText('Describa las anomalías detectadas, si las hay.'),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->visible(fn ($get) => \App\Filament\Resources\OrdenComprasInsumosResource\RelationManagers\DetallesRelationManager::esMateriaPrima($get('tipo_orden_compra_id')))
                                    ->columnSpanFull(),
                            ])
                            ->grid([
                                'default' => 4,
                                'sm' => 2,
                                'xs' => 1,
                            ])
                            ->itemLabel(fn (array $state): ?string => \App\Models\Productos::find($state['producto_id'])?->nombre ?? 'Nuevo detalle')
                            ->collapsible()
                            ->addActionLabel('Añadir Detalle')
                            ->deleteAction(
                                fn ($action) => $action->requiresConfirmation()->label('Eliminar Detalle')
                            )
                            ->hidden(fn ($context) => $context === 'edit' || $context === 'view')
                            ->extraAttributes(['class' => 'gap-4'])
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
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
                Tables\Columns\TextColumn::make('detalles_count')
                    ->label('Productos')
                    ->getStateUsing(fn ($record) => $record->detalles()->count())
                    ->formatStateUsing(fn ($state) => $state ?? '0')
                    ->sortable()
                    ->description(fn ($record) => $record->detalles()->count() . ' productos'),
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
                    ->falseIcon('heroicon-o-check-circle')
                    ->getStateUsing(fn ($record) => $record->detalles && $record->detalles->contains('anomalias', true))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Pendiente' => 'Orden Abierta',
                        'Recibida' => 'Orden en Inventario',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->where('estado', $data['value']);
                        }
                    }),
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
                        ->disabled(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida'),
                    Tables\Actions\Action::make('recibirEnInventario')
                        ->label('Recibir en Inventario')
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->color('success')
                        ->hidden(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida')
                        ->url(fn (OrdenComprasInsumos $record): string => \App\Filament\Pages\RecibirOrdenCompraInsumos::getUrl(['orden_id' => $record->id])),
                    Tables\Actions\Action::make('generatePdf')
                        ->label('Generar PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->hidden(fn (OrdenComprasInsumos $record): bool => $record->estado !== 'Recibida')
                        ->action(function (OrdenComprasInsumos $record) {
                            $pdf = Pdf::loadView('pdf.orden-compra-insumos', [
                                'orden' => $record->load(['empresa', 'proveedor', 'detalles.producto', 'detalles.tipoOrdenCompra']),
                                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                            ]);
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, "orden-compra-insumos-{$record->id}.pdf");
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->disabled(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida'),
                ])
                    ->label('Acciones')
                    ->button()
                    ->outlined()
                    ->dropdown(true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->requiresConfirmation()
                        ->disabled(fn ($records) => $records && $records->contains(fn ($record) => $record->estado === 'Recibida')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DetallesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenComprasInsumos::route('/'),
            'create' => Pages\CreateOrdenComprasInsumos::route('/create'),
            'edit' => Pages\EditOrdenComprasInsumos::route('/{record}/edit'),
            'view' => Pages\ViewOrdenComprasInsumos::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['detalles']);
    }
}
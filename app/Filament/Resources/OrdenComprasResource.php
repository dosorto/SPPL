<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasResource\Pages;
use App\Models\OrdenCompras;
use App\Models\TipoOrdenCompras;
use App\Models\OrdenComprasDetalle;
use App\Models\Proveedores; // Asegurarse de importar el modelo de Proveedores
use App\Models\Paises;
use App\Models\Departamento;
use App\Models\Municipio;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\RecibirOrdenCompra;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Livewire;
use Filament\Facades\Filament;

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
                        ->relationship('tipoOrdenCompra', 'nombre', function ($query) {
                            // Scope the relationship to only show TipoOrdenCompras for the current user's empresa_id
                            return $query->where('empresa_id', Auth::user()->empresa_id);
                        })
                        ->required()
                        ->searchable()
                        ->preload()
                        ->optionsLimit(100)
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, $livewire) {
                            $livewire->dispatch('updateFormState', [
                                'tipo_orden_compra_id' => $state,
                            ]);
                        })
                        ->createOptionForm([
                            Forms\Components\Section::make('Crear Nuevo Tipo de Orden')
                                ->schema([
                                    Forms\Components\TextInput::make('nombre')
                                        ->label('Nombre del Tipo de Orden')
                                        ->required()
                                        ->maxLength(100)
                                        ->placeholder('Ej. Maquinaria Lácteos, Insumos Lácteos...')
                                        ->unique(table: TipoOrdenCompras::class, column: 'nombre', ignoreRecord: true)
                                        ->helperText('Ejemplo: Maquinaria Lácteos, Materia Prima Lácteos'),
                                ]),
                        ])
                        ->createOptionAction(function ($action) {
                            return $action
                                ->modalHeading('Crear Tipo de Orden de Compra')
                                ->modalSubmitActionLabel('Crear')
                                ->modalWidth('lg')
                                ->icon('heroicon-o-plus');
                        })
                        ->createOptionUsing(function (array $data) {
                            // Create the TipoOrdenCompras record with the authenticated user's empresa_id
                            return TipoOrdenCompras::create([
                                'nombre' => $data['nombre'],
                                'empresa_id' => Auth::user()->empresa_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ])->id;
                        }),
                        Forms\Components\Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedor', 'nombre_proveedor')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(100)
                            ->placeholder('Seleccione un proveedor o presione "+"')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('createProveedor') // Utilizando el namespace completo
                                    ->label('Agregar')
                                    ->icon('heroicon-o-plus')
                                    ->tooltip('Agregar un nuevo proveedor')
                                    ->modalHeading('Crear Nuevo Proveedor')
                                    // CORRECCIÓN: Definimos los campos directamente para evitar el error de inicialización
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
                                            ->disabled(fn () => true)
                                            ->dehydrated(true)
                                            ->suffix(null),
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
                                        $set('empresa_id', $newProveedor->empresa_id); // Asumiendo que la empresa se asigna automáticamente al crear
                                    })
                                    ->slideOver()
                                    ->after(function () {
                                        // No se necesita ninguna acción aquí, Filament cerrará el modal automáticamente.
                                    })
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                $proveedor = Proveedores::find($state);
                                $set('empresa_id', $proveedor?->empresa_id ?? null);
                                $livewire->dispatch('updateFormState', [
                                    'proveedor_id' => $state,
                                    'empresa_id' => $proveedor?->empresa_id ?? null,
                                ]);
                            })
                            ->afterStateHydrated(function ($state, callable $set, $livewire) {
                                $proveedor = Proveedores::find($state);
                                $set('empresa_id', $proveedor?->empresa_id ?? null);
                                $livewire->dispatch('updateFormState', [
                                    'proveedor_id' => $state,
                                    'empresa_id' => $proveedor?->empresa_id ?? null,
                                ]);
                            }),
                        Forms\Components\Hidden::make('empresa_id')
                            ->required()
                            ->dehydrated(true),
                        Forms\Components\DatePicker::make('fecha_realizada')
                            ->label('Fecha Realizada')
                            ->required()
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(function ($state, $livewire) {
                                $livewire->dispatch('updateFormState', [
                                    'fecha_realizada' => $state,
                                ]);
                            }),
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->nullable()
                            ->maxLength(65535)
                            ->rows(4)
                            ->live()
                            ->afterStateUpdated(function ($state, $livewire) {
                                $livewire->dispatch('updateFormState', [
                                    'descripcion' => $state,
                                ]);
                            }),
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
                        Forms\Components\View::make('livewire.wrap-orden-compra-detalles-form')
                            ->label('Detalles de la Orden')
                            ->viewData(fn (\Filament\Forms\Get $get) => [
                                'record' => $get('id') ? \App\Models\OrdenCompras::with('detalles.producto')->find($get('id')) : null,
                            ])
                            ->columnSpanFull()
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
            ->defaultSort('id', 'desc')
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
                    Action::make('generatePdf')
                        ->label('Generar PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->hidden(fn (OrdenCompras $record): bool => $record->estado !== 'Recibida')
                        ->action(function (OrdenCompras $record) {
                            // Validate required relationships and data
                            if (!$record->proveedor) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('No se puede generar el PDF: El proveedor no está definido.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            if (!$record->empresa) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('No se puede generar el PDF: La empresa no está definida.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            if (!$record->tipoOrdenCompra) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('No se puede generar el PDF: El tipo de orden no está definido.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            if ($record->detalles->isEmpty()) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('No se puede generar el PDF: No hay detalles registrados para esta orden.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Generate PDF if all validations pass
                            try {
                                $pdf = Pdf::loadView('pdf.orden-compra', [
                                    'orden' => $record->load(['empresa', 'proveedor', 'tipoOrdenCompra', 'detalles.producto']),
                                    'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                                ]);
                                return response()->streamDownload(function () use ($pdf) {
                                    echo $pdf->output();
                                }, "orden-compra-{$record->id}.pdf");
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Ocurrió un error al generar el PDF: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
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
            //\App\Filament\Resources\OrdenComprasResource\RelationManagers\DetallesRelationManager::class,
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

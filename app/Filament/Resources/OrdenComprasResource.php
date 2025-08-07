<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasResource\Pages;
use App\Models\OrdenCompras;
use App\Models\TipoOrdenCompras;
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
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Livewire;

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
                            ->optionsLimit(100)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                $livewire->dispatch('updateFormState', [
                                    'tipo_orden_compra_id' => $state,
                                ]);
                            }),
                        Forms\Components\Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedor', 'nombre_proveedor')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(100)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                $proveedor = \App\Models\Proveedores::find($state);
                                $set('empresa_id', $proveedor?->empresa_id ?? null);
                                $livewire->dispatch('updateFormState', [
                                    'proveedor_id' => $state,
                                    'empresa_id' => $proveedor?->empresa_id ?? null,
                                ]);
                            })
                            ->afterStateHydrated(function ($state, callable $set, $livewire) {
                                $proveedor = \App\Models\Proveedores::find($state);
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
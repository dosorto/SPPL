<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Resources\InventarioProductosResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\OrdenCompras;
use App\Models\InventarioProductos;
use App\Models\Productos;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Attributes\Url;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Collection;

class RecibirOrdenCompra extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static string $view = 'filament.pages.recibir-orden-compra';
    protected static ?string $navigationLabel = 'Recibir por Orden';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $title = 'Recibir Mercancía de Orden de Compra';
    protected static bool $shouldRegisterNavigation = false;

    #[Url]
    public ?int $orden_id = null;
    
    public ?array $data = [];
    
    public ?OrdenCompras $orden = null;
    
    public function mount(): void
    {
        $this->form->fill();
        if ($this->orden_id) {
            $this->cargarDetallesOrden($this->orden_id);
        }
    }

    public function updatedOrdenId($value): void
    {
        $this->cargarDetallesOrden($value);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Buscar Orden de Compra')
                    ->hidden(fn (): bool => $this->orden_id !== null)
                    ->schema([
                        Forms\Components\TextInput::make('orden_id')
                            ->label('ID de la Orden de Compra')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state) => $this->cargarDetallesOrden($state)),
                    ]),

                Forms\Components\Section::make('Resumen de la Orden')
                    ->visible(fn (): bool => $this->orden !== null)
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('nombre_proveedor')
                                ->label('Proveedor')
                                ->disabled()->dehydrated(false),
                           Forms\Components\TextInput::make('fecha_realizada')
                                ->label('Fecha de Orden')
                                ->disabled()->dehydrated(false),
                            Forms\Components\TextInput::make('estado_orden')
                                ->label('Estado Actual')
                                ->disabled()->dehydrated(false),
                        ])
                    ]),

                Forms\Components\Section::make('Productos a Recibir y Precios')
                    ->visible(fn (callable $get) => !empty($get('detalles_orden')))
                    ->schema([
                        Forms\Components\Repeater::make('detalles_orden')
                            ->label(false)
                            ->schema([
                                Forms\Components\Grid::make(9)
                                    ->schema([
                                        Forms\Components\TextInput::make('producto_nombre')
                                            ->label('Producto')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan(2),
                                            
                                        Forms\Components\TextInput::make('cantidad')
                                            ->label('Cant.')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->required()
                                            ->columnSpan(1),
                                            
                                        Forms\Components\TextInput::make('precio')
                                            ->label('Costo')
                                            ->numeric()
                                            ->required()
                                            ->prefix('L.')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),
                                            
                                        Forms\Components\TextInput::make('porcentaje_ganancia')
                                            ->label('% Gan.')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->actualizarPrecios($get, $set))
                                            ->columnSpan(1),
                                            
                                        Forms\Components\TextInput::make('precio_detalle')
                                            ->label('P. Detalle')
                                            ->numeric()
                                            ->required()
                                            ->prefix('L.')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('porcentaje_ganancia_mayorista')
                                            ->label('% May.')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->actualizarPrecios($get, $set))
                                            ->columnSpan(1),
                                            
                                        Forms\Components\TextInput::make('precio_mayorista')
                                            ->label('P. Mayor.')
                                            ->numeric()
                                            ->required()
                                            ->prefix('L.')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),
                                        
                                        Forms\Components\TextInput::make('porcentaje_descuento')
                                            ->label('% Desc.')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->actualizarPrecios($get, $set))
                                            ->columnSpan(1),
                                    ]),
                                    
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('precio_promocion')
                                            ->label('Precio de Oferta')
                                            ->numeric()
                                            ->required()
                                            ->prefix('L.')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),
                                            
                                        Forms\Components\Placeholder::make('spacer')
                                            ->label('')
                                            ->content('')
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsed(false)
                            ->itemLabel(fn (array $state): ?string => $state['producto_nombre'] ?? 'Producto')
                            ->columnSpanFull(),
                    ]),
                
                Actions::make([
                    Action::make('confirmar')
                        ->label('Confirmar Inventario')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmar Recepción de Inventario')
                        ->modalDescription('¿Está seguro/a de hacer esto? Esta acción actualizará las existencias y precios de los productos.')
                        ->modalSubmitActionLabel('Sí, Confirmar')
                        ->modalCancelActionLabel('Cancelar')
                        ->modalIcon('heroicon-o-inbox-arrow-down')
                        ->modalIconColor('success')
                        ->action(function () {
                            $this->procesarInventario();
                        })
                ])->visible(fn (): bool => !empty($this->data['detalles_orden'] ?? []))

            ])
            ->statePath('data');
    }

    public function hasUnsavedDataChangesAlert(): bool
    {
        return false;
    }

    public function cargarDetallesOrden(?int $ordenId): void
    {
        if (empty($ordenId)) {
            $this->orden = null;
            $this->form->fill(['detalles_orden' => []]);
            return;
        }
        
        $orden = OrdenCompras::with(['detalles.producto', 'proveedor'])->find($ordenId);
        
        if ($orden && $orden->estado !== 'Recibida') {
            $this->orden = $orden;
            $detallesEnriquecidos = [];
            foreach ($orden->detalles as $detalle) {
                $costo = $detalle->precio;
                $precioDetalle = $costo * 1.30;
                $precioMayorista = $costo * 1.25;
                $precioPromocion = $precioDetalle * 0.85;

                $detallesEnriquecidos[] = [
                    'producto_id' => $detalle->producto_id,
                    'producto_nombre' => $detalle->producto->nombre,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $costo,
                    'porcentaje_ganancia' => 30,
                    'precio_detalle' => number_format($precioDetalle, 2, '.', ''),
                    'porcentaje_ganancia_mayorista' => 25,
                    'precio_mayorista' => number_format($precioMayorista, 2, '.', ''),
                    'porcentaje_descuento' => 15,
                    'precio_promocion' => number_format($precioPromocion, 2, '.', ''),
                ];
            }
            $this->form->fill([
                'orden_id' => $ordenId,
                'nombre_proveedor' => $orden->proveedor->nombre_proveedor ?? 'N/A',
                'fecha_realizada' => $orden->fecha_realizada->format('d/m/Y'),
                'estado_orden' => $orden->estado,
                'detalles_orden' => $detallesEnriquecidos
            ]);
        } else {
            $this->orden = null;
            $this->form->fill([
                'orden_id' => $ordenId,
                'detalles_orden' => []
            ]);
            if ($orden) {
                Notification::make()->warning()->title('Advertencia')->body('Esta orden ya fue recibida.')->send();
            } else {
                Notification::make()->danger()->title('Error')->body('Orden no encontrada.')->send();
            }
        }
    }

    public function actualizarPrecios(Get $get, Set $set): void
    {
        $costo = (float) $get('precio');
        $porcentajeGanancia = (float) $get('porcentaje_ganancia');
        $porcentajeDescuento = (float) $get('porcentaje_descuento');
        $porcentajeGananciaMayorista = (float) $get('porcentaje_ganancia_mayorista');

        if ($costo > 0) {
            $precioDetalle = $costo * (1 + $porcentajeGanancia / 100);
            $set('precio_detalle', number_format($precioDetalle, 2, '.', ''));

            $precioMayorista = $costo * (1 + $porcentajeGananciaMayorista / 100);
            $set('precio_mayorista', number_format($precioMayorista, 2, '.', ''));

            $precioPromocion = $precioDetalle * (1 - $porcentajeDescuento / 100);
            $set('precio_promocion', number_format($precioPromocion, 2, '.', ''));
        }
    }

    public function procesarInventario(): void
    {
        $data = $this->form->getState();
        $ordenId = $this->orden_id ?? $data['orden_id'] ?? null;

        if (!$ordenId) {
            Notification::make()->danger()->title('Error')->body('No se ha especificado una Orden de Compra.')->send();
            return;
        }

        $ordenCompra = OrdenCompras::find($ordenId);

        if (!$ordenCompra || $ordenCompra->estado === 'Recibida') {
            Notification::make()->danger()->title('Acción Fallida')->body('La orden no es válida o ya fue procesada.')->send();
            return;
        }

        foreach ($data['detalles_orden'] as $detalleEditable) {
            $inventario = InventarioProductos::firstOrCreate(
                ['producto_id' => $detalleEditable['producto_id']],
                ['cantidad' => 0, 'precio_costo' => 0, 'precio_detalle' => 0, 'precio_mayorista' => 0, 'precio_promocion' => 0]
            );
            
            $inventario->increment('cantidad', $detalleEditable['cantidad']);
            $inventario->precio_costo = $detalleEditable['precio'];
            $inventario->precio_detalle = $detalleEditable['precio_detalle'];
            $inventario->precio_mayorista = $detalleEditable['precio_mayorista'];
            $inventario->precio_promocion = $detalleEditable['precio_promocion'];
            $inventario->save();
        }

        $ordenCompra->update(['estado' => 'Recibida']);
        Notification::make()->success()->title('Éxito')->body('Inventario y precios actualizados correctamente.')->send();

        redirect()->to(InventarioProductosResource::getUrl('index'));
    }
}
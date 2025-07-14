<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\OrdenCompras;
use App\Models\InventarioProductos;
use App\Models\Productos; // Asegúrate de tener este 'use'

class RecibirOrdenCompra extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static string $view = 'filament.pages.recibir-orden-compra';
    protected static ?string $navigationLabel = 'Recibir por Orden';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $title = 'Recibir Mercancía de Orden de Compra';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Buscar Orden de Compra')
                    ->schema([
                        Forms\Components\TextInput::make('orden_id')
                            ->label('ID de la Orden de Compra')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (empty($state)) {
                                    $set('detalles_orden', []);
                                    return;
                                }
                                $orden = OrdenCompras::with('detalles.producto')->find($state);
                                if ($orden && $orden->estado !== 'Recibida') {
                                    $set('detalles_orden', $orden->detalles->toArray());
                                } else {
                                    $set('detalles_orden', []);
                                    if ($orden) {
                                        Notification::make()->warning()->title('Advertencia')->body('Esta orden ya fue recibida.')->send();
                                    } else {
                                        Notification::make()->danger()->title('Error')->body('Orden no encontrada.')->send();
                                    }
                                }
                            }),
                    ]),

                Forms\Components\Section::make('Productos a Recibir')
                    ->visible(fn (callable $get) => !empty($get('detalles_orden')))
                    ->schema([
                        Forms\Components\Repeater::make('detalles_orden')
                            ->schema([
                                // --- INICIO DE LA CORRECCIÓN ---
                                Forms\Components\Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(Productos::all()->pluck('nombre', 'id')) // Carga manual de opciones
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                                // --- FIN DE LA CORRECCIÓN ---
                                Forms\Components\TextInput::make('cantidad')
                                    ->label('Cantidad')->numeric()->required(),
                                Forms\Components\TextInput::make('precio')
                                    ->label('Costo')->numeric()->required()->prefix('LPS'),
                            ])
                            ->columns(3)
                            ->addable(false)->deletable(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $ordenCompra = OrdenCompras::find($data['orden_id']);

        if (!$ordenCompra || $ordenCompra->estado === 'Recibida') {
            Notification::make()->danger()->title('Acción Fallida')->body('La orden no es válida o ya fue procesada.')->send();
            return;
        }

        foreach ($data['detalles_orden'] as $detalleEditable) {
            $inventario = InventarioProductos::firstOrCreate(
                ['producto_id' => $detalleEditable['producto_id']],
                ['cantidad' => 0, 'precio_costo' => 0]
            );
            
            $costo = $detalleEditable['precio'];
            $inventario->increment('cantidad', $detalleEditable['cantidad']);
            $inventario->precio_costo = $costo;
            $inventario->precio_detalle = $costo * 1.30;
            $inventario->precio_promocion = ($costo * 1.30) * 0.85;
            $inventario->save();
        }

        $ordenCompra->update(['estado' => 'Recibida']);
        Notification::make()->success()->title('Éxito')->body('Inventario actualizado.')->send();

        $this->form->fill();
    }
}
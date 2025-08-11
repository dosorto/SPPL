<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\OrdenComprasInsumos;
use App\Models\OrdenComprasInsumosDetalle;
use App\Models\InventarioInsumos;
use Livewire\Attributes\Url;

class RecibirOrdenCompraInsumos extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static string $view = 'filament.pages.recibir-orden-compra-insumos';
    protected static ?string $navigationLabel = 'Recibir Orden de Compra Insumos';
    protected static ?string $navigationGroup = 'Insumos y Materia Prima';
    protected static ?string $title = 'Recibir Orden de Compra de Insumos';
    protected static bool $shouldRegisterNavigation = false;

    #[Url]
    public ?int $orden_id = null;

    public ?OrdenComprasInsumos $orden = null;

    public function mount(): void
    {
        if ($this->orden_id) {
            $this->cargarOrden($this->orden_id);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('titulo')
                    ->content(fn () => $this->orden
                        ? 'Orden #' . $this->orden->id . ' - ' . $this->orden->proveedor?->nombre_proveedor
                        : 'Seleccione una orden'),
            ])
            ->statePath('data');
    }

    public function cargarOrden(int $ordenId): void
    {
        $orden = OrdenComprasInsumos::with(['detalles.producto', 'detalles.tipoOrdenCompra', 'proveedor'])->find($ordenId);

        if (!$orden) {
            Notification::make()->danger()->title('Error')->body('Orden no encontrada.')->send();
            $this->redirectRoute('filament.admin.resources.orden-compras-insumos.index');
            return;
        }

        if ($orden->estado === 'Recibida') {
            Notification::make()->warning()->title('Advertencia')->body('Esta orden ya fue recibida.')->send();
            $this->redirectRoute('filament.admin.resources.orden-compras-insumos.index');
            return;
        }

        $this->orden = $orden;
    }

    public function recibir(): void
    {
        if (!$this->orden || $this->orden->estado === 'Recibida') {
            Notification::make()->danger()->title('No se puede procesar')->body('Orden no válida o ya recibida.')->send();
            return;
        }

        foreach ($this->orden->detalles as $detalle) {
            $inventario = InventarioInsumos::firstOrCreate(
                [
                    'producto_id' => $detalle->producto_id,
                    'empresa_id' => $this->orden->empresa_id,
                ],
                [
                    'cantidad' => 0,
                    'precio_costo' => $detalle->precio_unitario,
                ]
            );

            $inventario->cantidad += $detalle->cantidad;
            $inventario->precio_costo = $detalle->precio_unitario;
            $inventario->save();
        }

        $this->orden->update(['estado' => 'Recibida']);

        Notification::make()->success()->title('Éxito')->body('La orden ha sido recibida y el inventario actualizado.')->send();

        $this->redirectRoute('filament.admin.resources.orden-compras-insumos.index');
    }
}
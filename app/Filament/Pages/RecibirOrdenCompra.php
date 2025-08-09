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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;

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
    
    public array $productosData = [];
    
    // Propiedades para paginación
    public int $currentPage = 1;
    public int $perPage = 10;
    public int $totalPages = 1;
    
    // Propiedad para el filtro de búsqueda
    public string $searchFilter = '';
    
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

    // Método que se ejecuta cuando cambia el filtro de búsqueda
    public function updatedSearchFilter(): void
    {
        $this->currentPage = 1; // Resetear a la primera página cuando se filtra
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

               Section::make('Resumen de la Orden')
                ->visible(fn (): bool => $this->orden !== null)
                ->compact() 
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('nombre_proveedor')
                            ->label('Proveedor')
                            ->content(fn (): ?string => $this->orden?->proveedor?->nombre_proveedor), 

                        Placeholder::make('fecha_realizada')
                            ->label('Fecha de Orden')
                            ->content(fn (): ?string => $this->orden?->fecha_realizada?->format('d/m/Y')),

                        Placeholder::make('estado_orden')
                            ->label('Estado Actual')
                            ->content(fn (): ?string => $this->orden?->estado),
                        
                        Placeholder::make('descripcion')
                            ->label('Descripción')
                            ->content(fn (): ?string => $this->orden?->descripcion)
                            ->columnSpan(3), 
                    ]),
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
                ])->visible(fn (): bool => !empty($this->productosData))

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
            $this->productosData = [];
            $this->form->fill([]);
            return;
        }
        
        $orden = OrdenCompras::with(['detalles.producto', 'proveedor'])->find($ordenId);
        
        if ($orden && $orden->estado !== 'Recibida') {
            $this->orden = $orden;
            $this->productosData = [];
            
            foreach ($orden->detalles as $detalle) {
                $costo = $detalle->precio;
                $precioDetalle = $costo * 1.30;
                $precioMayorista = $costo * 1.25;
                $precioPromocion = $precioDetalle * 0.85;

                $this->productosData[] = [
                    'id' => $detalle->id,
                    'producto_id' => $detalle->producto_id,
                    'producto_nombre' => $detalle->producto->nombre,
                    'producto_sku' => $detalle->producto->sku ?? '', // Agregamos el SKU
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
                'descripcion' => $orden->descripcion,
                'tipo_orden_nombre' => $orden->tipoOrdenNombre,
            ]);
        } else {
            $this->orden = null;
            $this->productosData = [];
            $this->form->fill([
                'orden_id' => $ordenId,
            ]);
            if ($orden) {
                Notification::make()->warning()->title('Advertencia')->body('Esta orden ya fue recibida.')->send();
            } else {
                Notification::make()->danger()->title('Error')->body('Orden no encontrada.')->send();
            }
        }
    }

    public function actualizarPrecios($index): void
    {
        if (!isset($this->productosData[$index])) {
            return;
        }

        $costo = (float) $this->productosData[$index]['precio'];
        $porcentajeGanancia = (float) $this->productosData[$index]['porcentaje_ganancia'];
        $porcentajeDescuento = (float) $this->productosData[$index]['porcentaje_descuento'];
        $porcentajeGananciaMayorista = (float) $this->productosData[$index]['porcentaje_ganancia_mayorista'];

        if ($costo > 0) {
            // Solo calcular automáticamente si el precio no fue editado manualmente
            if (!isset($this->productosData[$index]['precio_detalle_manual']) || !$this->productosData[$index]['precio_detalle_manual']) {
                $precioDetalle = $costo * (1 + $porcentajeGanancia / 100);
                $this->productosData[$index]['precio_detalle'] = number_format($precioDetalle, 2, '.', '');
            }
            
            if (!isset($this->productosData[$index]['precio_mayorista_manual']) || !$this->productosData[$index]['precio_mayorista_manual']) {
                $precioMayorista = $costo * (1 + $porcentajeGananciaMayorista / 100);
                $this->productosData[$index]['precio_mayorista'] = number_format($precioMayorista, 2, '.', '');
            }

            if (!isset($this->productosData[$index]['precio_promocion_manual']) || !$this->productosData[$index]['precio_promocion_manual']) {
                $precioDetalle = (float) $this->productosData[$index]['precio_detalle'];
                $precioPromocion = $precioDetalle * (1 - $porcentajeDescuento / 100);
                $this->productosData[$index]['precio_promocion'] = number_format($precioPromocion, 2, '.', '');
            }
        }
    }

    public function actualizarPrecioManual($index, $tipoPrecio): void
    {
        if (!isset($this->productosData[$index])) {
            return;
        }

        $this->productosData[$index][$tipoPrecio . '_manual'] = true;
        
        if ($tipoPrecio === 'precio_detalle') {
            $costo = (float) $this->productosData[$index]['precio'];
            $precioDetalle = (float) $this->productosData[$index]['precio_detalle'];
            if ($costo > 0) {
                $porcentaje = (($precioDetalle / $costo) - 1) * 100;
                $this->productosData[$index]['porcentaje_ganancia'] = number_format($porcentaje, 2, '.', '');
            }
        }
        
        if ($tipoPrecio === 'precio_mayorista') {
            $costo = (float) $this->productosData[$index]['precio'];
            $precioMayorista = (float) $this->productosData[$index]['precio_mayorista'];
            if ($costo > 0) {
                $porcentaje = (($precioMayorista / $costo) - 1) * 100;
                $this->productosData[$index]['porcentaje_ganancia_mayorista'] = number_format($porcentaje, 2, '.', '');
            }
        }
        
        if ($tipoPrecio === 'precio_promocion') {
            $precioDetalle = (float) $this->productosData[$index]['precio_detalle'];
            $precioPromocion = (float) $this->productosData[$index]['precio_promocion'];
            if ($precioDetalle > 0) {
                $porcentaje = (1 - ($precioPromocion / $precioDetalle)) * 100;
                $this->productosData[$index]['porcentaje_descuento'] = number_format($porcentaje, 2, '.', '');
            }
        }
    }


    public function actualizarPrecioPorPorcentaje($index, $tipoPorcentaje): void
    {
        if (!isset($this->productosData[$index])) {
            return;
        }

        $costo = (float) $this->productosData[$index]['precio'];
        
        if ($tipoPorcentaje === 'porcentaje_ganancia') {

            $this->productosData[$index]['precio_detalle_manual'] = false;
            
            $porcentajeGanancia = (float) $this->productosData[$index]['porcentaje_ganancia'];
            if ($costo > 0) {
                $precioDetalle = $costo * (1 + $porcentajeGanancia / 100);
                $this->productosData[$index]['precio_detalle'] = number_format($precioDetalle, 2, '.', '');
                

                if (!isset($this->productosData[$index]['precio_promocion_manual']) || !$this->productosData[$index]['precio_promocion_manual']) {
                    $porcentajeDescuento = (float) $this->productosData[$index]['porcentaje_descuento'];
                    $precioPromocion = $precioDetalle * (1 - $porcentajeDescuento / 100);
                    $this->productosData[$index]['precio_promocion'] = number_format($precioPromocion, 2, '.', '');
                }
            }
        }
        
        if ($tipoPorcentaje === 'porcentaje_ganancia_mayorista') {
            // Resetear el flag manual para precio mayorista
            $this->productosData[$index]['precio_mayorista_manual'] = false;
            
            $porcentajeGananciaMayorista = (float) $this->productosData[$index]['porcentaje_ganancia_mayorista'];
            if ($costo > 0) {
                $precioMayorista = $costo * (1 + $porcentajeGananciaMayorista / 100);
                $this->productosData[$index]['precio_mayorista'] = number_format($precioMayorista, 2, '.', '');
            }
        }
        
        if ($tipoPorcentaje === 'porcentaje_descuento') {
            // Resetear el flag manual para precio promoción
            $this->productosData[$index]['precio_promocion_manual'] = false;
            
            $precioDetalle = (float) $this->productosData[$index]['precio_detalle'];
            $porcentajeDescuento = (float) $this->productosData[$index]['porcentaje_descuento'];
            if ($precioDetalle > 0) {
                $precioPromocion = $precioDetalle * (1 - $porcentajeDescuento / 100);
                $this->productosData[$index]['precio_promocion'] = number_format($precioPromocion, 2, '.', '');
            }
        }
    }

    // Método para filtrar productos
    public function getProductosFiltrados()
    {
        if (empty($this->searchFilter)) {
            return $this->productosData;
        }

        $filtro = strtolower($this->searchFilter);
        
        return array_filter($this->productosData, function($producto) use ($filtro) {
            $nombre = strtolower($producto['producto_nombre'] ?? '');
            $sku = strtolower($producto['producto_sku'] ?? '');
            
            return str_contains($nombre, $filtro) || str_contains($sku, $filtro);
        });
    }

    public function getProductosPaginados()
    {
        $productosFiltrados = $this->getProductosFiltrados();
        $this->totalPages = ceil(count($productosFiltrados) / $this->perPage);
        $offset = ($this->currentPage - 1) * $this->perPage;
        return array_slice($productosFiltrados, $offset, $this->perPage, true);
    }

    // Método para obtener el total de productos filtrados
    public function getTotalProductosFiltrados()
    {
        return count($this->getProductosFiltrados());
    }

    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function nextPage(): void
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
        }
    }

    public function goToPage($page): void
    {
        if ($page >= 1 && $page <= $this->totalPages) {
            $this->currentPage = $page;
        }
    }

    // Método para limpiar el filtro
    public function limpiarFiltro(): void
    {
        $this->searchFilter = '';
        $this->currentPage = 1;
    }

    public function procesarInventario(): void
    {
        $ordenId = $this->orden_id ?? $this->data['orden_id'] ?? null;

        if (!$ordenId) {
            Notification::make()->danger()->title('Error')->body('No se ha especificado una Orden de Compra.')->send();
            return;
        }

        $ordenCompra = OrdenCompras::find($ordenId);

        if (!$ordenCompra || $ordenCompra->estado === 'Recibida') {
            Notification::make()->danger()->title('Acción Fallida')->body('La orden no es válida o ya fue procesada.')->send();
            return;
        }

        foreach ($this->productosData as $detalleEditable) {
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
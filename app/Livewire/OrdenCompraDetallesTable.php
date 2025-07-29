<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;
use Filament\Forms\Concerns\InteractsWithForms; // Required for form interaction methods if needed
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class OrdenCompraDetallesTable extends Component implements HasForms
{
    use InteractsWithForms; // Use the trait

    public array $detalles = [];

    // This method is called when the component is first mounted or hydrated
    public function mount(array $detalles = [])
    {
        $this->detalles = $detalles;
    }

    // This method is called when the 'detalles' property is updated from the parent Filament form
    public function updatedDetalles($value)
    {
        $this->detalles = $value;
    }

    // New method to remove a product from the list
    public function removeProduct(int $index)
    {
        if (isset($this->detalles[$index])) {
            unset($this->detalles[$index]);
            $this->detalles = array_values($this->detalles); // Re-index the array
            $this->dispatch('update-parent-detalles', $this->detalles); // Dispatch event to update parent form
            Notification::make()
                ->title('Producto eliminado')
                ->body('El producto ha sido eliminado de la lista.')
                ->success()
                ->send();
        }
    }

    public function render()
    {
        $detallesForDisplay = collect($this->detalles)->map(function ($item) {
            if (isset($item['producto_id']) && $item['producto_id']) {
                $product = \App\Models\Productos::find($item['producto_id']);
                $item['producto_nombre'] = $product ? $product->nombre : 'Producto Desconocido';
                $item['producto_sku'] = $product ? $product->sku : 'N/A';
            } else {
                $item['producto_nombre'] = 'Seleccionar Producto';
                $item['producto_sku'] = 'N/A';
            }
            // Calculate subtotal for display
            $item['subtotal'] = ($item['cantidad'] ?? 0) * ($item['precio'] ?? 0);
            return $item;
        })->toArray();

        return view('livewire.orden-compra-detalles-table', [
            'detallesForDisplay' => $detallesForDisplay
        ]);
    }
}
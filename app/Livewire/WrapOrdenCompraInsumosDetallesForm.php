<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\OrdenComprasInsumos;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

class WrapOrdenCompraInsumosDetallesForm extends Component
{
    public $record;
    public $detalles = [];
    public $producto_id;
    public $cantidad;
    public $precio_unitario;

    public function mount($record = null)
    {
        $this->record = $record;
        if ($record) {
            $this->detalles = $record->detalles->toArray();
        }
    }

    public function addDetalle()
    {
        $this->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $this->detalles[] = [
            'producto_id' => $this->producto_id,
            'cantidad' => $this->cantidad,
            'precio_unitario' => $this->precio_unitario,
            'subtotal' => $this->cantidad * $this->precio_unitario,
        ];

        $this->reset(['producto_id', 'cantidad', 'precio_unitario']);
    }

    public function removeDetalle($index)
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);
    }

    public function saveDetalles()
    {
        if ($this->record) {
            $this->record->detalles()->delete();
            foreach ($this->detalles as $detalle) {
                $this->record->detalles()->create([
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.wrap-orden-compra-insumos-detalles-form', [
            'productos' => Producto::all(),
        ]);
    }
}
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Productos;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;
use Livewire\Livewire;


class OrdenCompraDetallesForm extends Component
{
    public $producto_id;
    public $cantidad = 1;
    public $precio = 0;

    public $detalles = [];
    public ?int $ordenId = null;
    //public $ordenId = null;



    // Estado de los campos básicos
    public $formState = [
        'tipo_orden_compra_id' => null,
        'proveedor_id' => null,
        'empresa_id' => null,
        'fecha_realizada' => null,
        'descripcion' => null,
    ];

    protected $listeners = [
        'updateFormState' => 'updateFormState',
    ];

    protected $rules = [
        'producto_id' => 'required|exists:productos,id',
        'cantidad' => 'required|numeric|min:1',
        'precio' => 'required|numeric|min:0',
        'formState.tipo_orden_compra_id' => 'required',
        'formState.proveedor_id' => 'required',
        'formState.empresa_id' => 'required',
        'formState.fecha_realizada' => 'required|date',
    ];

    protected $messages = [
        'producto_id.required' => 'Debe seleccionar un producto.',
        'producto_id.exists' => 'El producto seleccionado no es válido.',
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.numeric' => 'La cantidad debe ser un número.',
        'cantidad.min' => 'La cantidad debe ser al menos 1.',
        'precio.required' => 'El precio es obligatorio.',
        'precio.numeric' => 'El precio debe ser un número.',
        'precio.min' => 'El precio no puede ser negativo.',
        'formState.tipo_orden_compra_id.required' => 'Debe seleccionar un tipo de orden.',
        'formState.proveedor_id.required' => 'Debe seleccionar un proveedor.',
        'formState.empresa_id.required' => 'La empresa es obligatoria.',
        'formState.fecha_realizada.required' => 'La fecha es obligatoria.',
        'formState.fecha_realizada.date' => 'La fecha no es válida.',
    ];

    public function mount()
{
    if ($this->ordenId) {
        $orden = \App\Models\OrdenCompras::with('detalles.producto')->find($this->ordenId);
        if ($orden) {
            $this->formState['tipo_orden_compra_id'] = $orden->tipo_orden_compra_id;
            $this->formState['proveedor_id'] = $orden->proveedor_id;
            $this->formState['empresa_id'] = $orden->empresa_id;
            $this->formState['fecha_realizada'] = $orden->fecha_realizada;
            $this->formState['descripcion'] = $orden->descripcion;

            foreach ($orden->detalles as $detalle) {
                $this->detalles[] = [
                    'producto_id' => $detalle->producto_id,
                    'nombre_producto' => $detalle->producto->nombre ?? 'Desconocido',
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio,
                    'detalle_id' => $detalle->id,
                ];
            }

            // 🔁 Notificar al frontend que el estado está completo
            $this->dispatch('updateFormState', $this->formState);
        }
    }
}



    public function updateFormState($data)
    {
        foreach ($data as $key => $value) {
            $this->formState[$key] = $value;
        }
    }

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function addProducto()
    {
        $this->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:1',
            'precio' => 'required|numeric|min:0',
        ]);

        $producto = Productos::find($this->producto_id);

        if (!$producto) {
            Notification::make()
                ->title('Error')
                ->body('El producto seleccionado no existe.')
                ->danger()
                ->send();
            return;
        }

        $this->detalles[] = [
            'producto_id' => $producto->id,
            'nombre_producto' => $producto->nombre,
            'cantidad' => $this->cantidad,
            'precio' => $this->precio,
        ];

        // Guardar en sesión para ser usado en CreateOrdenCompras
        session()->put('detalles_orden', $this->detalles);

        // Reset inputs
        $this->reset(['producto_id', 'cantidad', 'precio']);
        $this->cantidad = 1;
        $this->precio = 0;

        // Emitir evento si quieres notificar al usuario o actualizar algo
        $this->dispatch('productoAdded');
    }

    public function editDetalle($index)
    {
        $detalle = $this->detalles[$index];

        $this->producto_id = $detalle['producto_id'];
        $this->cantidad = $detalle['cantidad'];
        $this->precio = $detalle['precio'];
        $this->editIndex = $index;
    }


    public function loadDetallesDesdeOrden($data)
    {
        $orden = \App\Models\OrdenCompras::with('detalles.producto')->find($data['orden_id']);

        if (!$orden) return;

        $this->formState = [
            'tipo_orden_compra_id' => $orden->tipo_orden_compra_id,
            'proveedor_id' => $orden->proveedor_id,
            'empresa_id' => $orden->empresa_id,
            'fecha_realizada' => $orden->fecha_realizada,
            'descripcion' => $orden->descripcion,
        ];

        $this->detalles = $orden->detalles->map(function ($item) {
            return [
                'producto_id' => $item->producto_id,
                'nombre_producto' => $item->producto->nombre ?? '',
                'cantidad' => $item->cantidad,
                'precio' => $item->precio,
            ];
        })->toArray();
    }
    public function loadOrden($orden_id)
    {
        $this->orden_id = $orden_id;
        $orden = Orden::with('detalles')->findOrFail($orden_id);

        // Cargar datos generales
        $this->tipo_orden = $orden->tipo_orden;
        $this->proveedor_id = $orden->proveedor_id;
        $this->fecha_realizada = $orden->fecha_realizada;
        $this->descripcion = $orden->descripcion;

        // Cargar detalles
        $this->detalles = $orden->detalles->map(function ($detalle) {
            return [
                'producto_id' => $detalle->producto_id,
                'cantidad' => $detalle->cantidad,
                'precio' => $detalle->precio,
            ];
        })->toArray();
    }


    public function removeDetalle($index)
    {
        $detalle = $this->detalles[$index] ?? null;

        // Si es un detalle guardado (tiene ID), lo borramos de la base de datos
        if (isset($detalle['detalle_id'])) {
            \App\Models\OrdenComprasDetalles::find($detalle['detalle_id'])?->delete();
        }

        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);
        $this->dispatch('detalleRemoved');
    }


    public function render()
    {
        $isBasicInfoComplete = !empty($this->formState['tipo_orden_compra_id']) &&
                               !empty($this->formState['proveedor_id']) &&
                               !empty($this->formState['empresa_id']) &&
                               !empty($this->formState['fecha_realizada']);

        return view('livewire.orden-compra-detalles-form', [
            'productos' => Productos::pluck('nombre', 'id'),
            'isBasicInfoComplete' => $isBasicInfoComplete,
        ]);
    }

    public function boot()
    {
        Livewire::component('orden-compra-detalles-form', \App\Livewire\OrdenCompraDetallesForm::class);
    }
    

}

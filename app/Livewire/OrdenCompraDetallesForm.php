<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Productos;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;

class OrdenCompraDetallesForm extends Component
{
    public $producto_id;
    public $producto_nombre = '';
    public $cantidad = 1;
    public $precio = 0;

    public $detalles = [];
    public ?int $ordenId = null;
    public $editIndex = null;

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

    public function mount($ordenId = null)
    {
        logger('MOUNT ORDEN ID:', ['id' => $ordenId]);
        $this->ordenId = $ordenId;

        if ($this->ordenId) {
            $orden = \App\Models\OrdenCompras::with('detalles.producto')->find($this->ordenId);
            if ($orden) {
                $this->formState = [
                    'tipo_orden_compra_id' => $orden->tipo_orden_compra_id,
                    'proveedor_id' => $orden->proveedor_id,
                    'empresa_id' => $orden->empresa_id,
                    'fecha_realizada' => $orden->fecha_realizada,
                    'descripcion' => $orden->descripcion,
                ];

                foreach ($orden->detalles as $detalle) {
                    $this->detalles[] = [
                        'producto_id' => $detalle->producto_id,
                        'nombre_producto' => $detalle->producto->nombre ?? 'Desconocido',
                        'cantidad' => $detalle->cantidad,
                        'precio' => $detalle->precio,
                        'detalle_id' => $detalle->id,
                    ];
                }

                session()->put('detalles_orden', $this->detalles);
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

    public function updateProductoId()
    {
        $productos = Productos::get()->mapWithKeys(function ($p) {
            return [$p->id => "{$p->sku} - {$p->nombre}"];
        });

        $selectedId = array_search($this->producto_nombre, $productos->all());
        $this->producto_id = $selectedId !== false ? $selectedId : '';

        // Validar inmediatamente después de actualizar
        $this->validateOnly('producto_id');
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

        // 👉 Si estamos editando, actualizamos la fila en lugar de agregar una nueva
        if ($this->editIndex !== null) {
            $this->detalles[$this->editIndex] = [
                'producto_id' => $producto->id,
                'nombre_producto' => $producto->nombre,
                'cantidad' => $this->cantidad,
                'precio' => $this->precio,
                'detalle_id' => $this->detalles[$this->editIndex]['detalle_id'] ?? null,
            ];
            $this->editIndex = null;
        } else {
            // 👉 Buscar si el producto ya está agregado
            $indexExistente = collect($this->detalles)->search(fn($item) => $item['producto_id'] == $producto->id);

            if ($indexExistente !== false) {
                // Sumar cantidades si ya existe
                $this->detalles[$indexExistente]['cantidad'] += $this->cantidad;
                // Actualizar el precio también si lo deseas
                $this->detalles[$indexExistente]['precio'] = $this->precio;
            } else {
                // Agregar nuevo producto
                $this->detalles[] = [
                    'producto_id' => $producto->id,
                    'nombre_producto' => $producto->nombre,
                    'cantidad' => $this->cantidad,
                    'precio' => $this->precio,
                ];
            }
        }

        session()->put('detalles_orden', $this->detalles);

        $this->reset(['producto_id', 'producto_nombre', 'cantidad', 'precio']);
        $this->cantidad = 1;
        $this->precio = 0;

        $this->dispatch('productoAdded');
    }



    public function editDetalle($index)
    {
        $detalle = $this->detalles[$index];

        $this->producto_id = $detalle['producto_id'];
        $this->producto_nombre = $detalle['nombre_producto'];
        $this->cantidad = $detalle['cantidad'];
        $this->precio = $detalle['precio'];
        $this->editIndex = $index;
    }

    public function removeDetalle($index)
    {
        $detalle = $this->detalles[$index] ?? null;

        if (isset($detalle['detalle_id'])) {
            \App\Models\OrdenComprasDetalle::find($detalle['detalle_id'])?->delete();
        }

        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);

        // 💡 Actualiza la sesión
        session()->put('detalles_orden', $this->detalles);

        $this->dispatch('detalleRemoved');
    }


    public function render()
    {
        $isBasicInfoComplete = !empty($this->formState['tipo_orden_compra_id']) &&
                               !empty($this->formState['proveedor_id']) &&
                               !empty($this->formState['empresa_id']) &&
                               !empty($this->formState['fecha_realizada']);

        $productos = Productos::get()->mapWithKeys(function ($p) {
            return [$p->id => "{$p->sku} - {$p->nombre}"];
        });

        if (!empty($this->producto_nombre)) {
            $productos = $productos->filter(function ($nombre, $id) {
                return stripos($nombre, $this->producto_nombre) !== false;
            });
        }

        return view('livewire.orden-compra-detalles-form', [
            'productos' => $productos,
            'isBasicInfoComplete' => $isBasicInfoComplete,
        ]);
    }

    public function getIsBasicInfoCompleteProperty()
    {
        return !empty($this->formState['tipo_orden_compra_id']) &&
            !empty($this->formState['proveedor_id']) &&
            !empty($this->formState['empresa_id']) &&
            !empty($this->formState['fecha_realizada']);
    }
}
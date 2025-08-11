<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Productos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;

class OrdenCompraDetallesForm extends Component
{
    public $producto_id;
    public $producto_nombre = '';
    public $cantidad = 1;
    public $precio = 0;

    public $detalles = [];
    public ?int $ordenId = null;
    public $editIndex = null;

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
        if ($property !== 'producto_nombre') {
            $this->validateOnly($property);
        }
    }

    public function updateProductoId()
    {
        if (empty($this->producto_nombre)) {
            $this->producto_id = null;
            return;
        }

        try {
            $nombre = $this->producto_nombre;

            $producto = Productos::where('empresa_id', Auth::user()->empresa_id)
                ->where(function ($query) use ($nombre) {
                    $query->where('sku', 'LIKE', "%{$nombre}%")
                          ->orWhere('nombre', 'LIKE', "%{$nombre}%")
                          ->orWhere('codigo', 'LIKE', "%{$nombre}%")
                          ->orWhereRaw("CONCAT(sku, ' - ', nombre) LIKE ?", ["%{$nombre}%"]);
                })
                ->first();

            $this->producto_id = $producto ? $producto->id : null;

            if (!$producto) {
                $this->producto_nombre = '';
                Notification::make()
                    ->title('Advertencia')
                    ->body('El producto no se encontró. Por favor, selecciona un producto de la lista o ingresa un SKU, nombre o código válido.')
                    ->warning()
                    ->send();
            }
        } catch (QueryException $e) {
            logger('Error en búsqueda de producto:', ['error' => $e->getMessage(), 'input' => $this->producto_nombre]);
            $this->producto_id = null;
            $this->producto_nombre = '';
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al buscar el producto. Por favor, intenta de nuevo.')
                ->danger()
                ->send();
        }
    }

    public function addProducto()
    {
        $this->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:1',
            'precio' => 'required|numeric|min:0',
        ]);

        try {
            $producto = Productos::where('empresa_id', Auth::user()->empresa_id)
                ->find($this->producto_id);

            if (!$producto) {
                Notification::make()
                    ->title('Error')
                    ->body('El producto seleccionado no pertenece a tu empresa.')
                    ->danger()
                    ->send();
                return;
            }

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
                $indexExistente = collect($this->detalles)->search(fn($item) => $item['producto_id'] == $producto->id);

                if ($indexExistente !== false) {
                    $this->detalles[$indexExistente]['cantidad'] += $this->cantidad;
                    $this->detalles[$indexExistente]['precio'] = $this->precio;
                } else {
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
        } catch (QueryException $e) {
            logger('Error al agregar producto:', ['error' => $e->getMessage(), 'producto_id' => $this->producto_id]);
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al agregar el producto. Por favor, intenta de nuevo.')
                ->danger()
                ->send();
        }
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
            try {
                \App\Models\OrdenComprasDetalle::find($detalle['detalle_id'])?->delete();
            } catch (QueryException $e) {
                logger('Error al eliminar detalle:', ['error' => $e->getMessage(), 'detalle_id' => $detalle['detalle_id']]);
                Notification::make()
                    ->title('Error')
                    ->body('Ocurrió un error al eliminar el detalle. Por favor, intenta de nuevo.')
                    ->danger()
                    ->send();
                return;
            }
        }

        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);

        session()->put('detalles_orden', $this->detalles);

        $this->dispatch('detalleRemoved');
    }

    public function render()
    {
        $isBasicInfoComplete = !empty($this->formState['tipo_orden_compra_id']) &&
                               !empty($this->formState['proveedor_id']) &&
                               !empty($this->formState['empresa_id']) &&
                               !empty($this->formState['fecha_realizada']);

        try {
            $productos = Productos::where('empresa_id', Auth::user()->empresa_id)
                ->when(!empty($this->producto_nombre), function ($query) {
                    $nombre = $this->producto_nombre;
                    $query->where(function ($q) use ($nombre) {
                        $q->where('sku', 'LIKE', "%{$nombre}%")
                          ->orWhere('nombre', 'LIKE', "%{$nombre}%")
                          ->orWhere('codigo', 'LIKE', "%{$nombre}%")
                          ->orWhereRaw("CONCAT(sku, ' - ', nombre) LIKE ?", ["%{$nombre}%"]);
                    });
                })
                ->get()
                ->mapWithKeys(fn($p) => [$p->id => "{$p->sku} - {$p->nombre}"]);

        } catch (QueryException $e) {
            logger('Error al cargar productos:', ['error' => $e->getMessage()]);
            $productos = collect([]);
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al cargar los productos. Por favor, intenta de nuevo.')
                ->danger()
                ->send();
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

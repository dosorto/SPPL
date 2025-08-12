<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Productos;
use Illuminate\Support\Facades\Auth;
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

    // Controla si el dropdown está abierto (entangle con Alpine)
    public $dropdownOpen = false;

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
        // ... (tus otros mensajes)
    ];

    public function mount($ordenId = null)
    {
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

    // Se ejecuta cada vez que cambia producto_nombre
    public function updatedProductoNombre($value)
    {
        // Si el usuario está escribiendo, limpiamos producto_id para evitar validaciones falsas
        $this->producto_id = null;

        // Abrimos el dropdown si hay texto (puedes ajustar la longitud mínima si quieres)
        $this->dropdownOpen = !empty($value);
    }

    // Selección desde el dropdown (solo recibe el id para evitar problemas con comillas)
    public function selectProducto($id)
    {
        try {
            $query = Productos::query();
            // mantén la excepción para root (id === 1). Ajusta si usas roles.
            if (Auth::user()->id !== 1) {
                $query->where('empresa_id', Auth::user()->empresa_id);
            }

            $producto = $query->find($id);

            if (! $producto) {
                Notification::make()
                    ->title('Error')
                    ->body('El producto seleccionado no está disponible para tu empresa.')
                    ->danger()
                    ->send();
                return;
            }

            $this->producto_id = $producto->id;
            $this->producto_nombre = "{$producto->sku} - {$producto->nombre}";
            $this->dropdownOpen = false;
        } catch (QueryException $e) {
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al seleccionar el producto.')
                ->danger()
                ->send();
        }
    }

    // Método que se puede disparar al perder foco si quieres validar/buscar coincidencia
    public function updateProductoId()
    {
        if (empty($this->producto_nombre)) {
            $this->producto_id = null;
            return;
        }

        try {
            $nombre = $this->producto_nombre;

            $productosQuery = Productos::query();
            if (Auth::user()->id !== 1) {
                $productosQuery->where('empresa_id', Auth::user()->empresa_id);
            }

            $producto = $productosQuery
                ->where(function ($query) use ($nombre) {
                    $query->where('sku', 'LIKE', "%{$nombre}%")
                          ->orWhere('nombre', 'LIKE', "%{$nombre}%")
                          ->orWhere('codigo', 'LIKE', "%{$nombre}%")
                          ->orWhereRaw("CONCAT(sku, ' - ', nombre) LIKE ?", ["%{$nombre}%"]);
                })
                ->first();

            $this->producto_id = $producto ? $producto->id : null;

            if (!$producto) {
                // Si no se encontró, opcional: comentarlo si no quieres limpiar el texto
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
            $productoQuery = Productos::query();

            if (Auth::user()->id !== 1) {
                $productoQuery->where('empresa_id', Auth::user()->empresa_id);
            }

            $producto = $productoQuery->find($this->producto_id);

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
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al agregar el producto. Por favor, intenta de nuevo.')
                ->danger()
                ->send();
        }
    }

    // editDetalle, removeDetalle se mantienen igual (omitidos aquí por brevedad, pero los mantén)
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
            $productosQuery = Productos::query();

            if (Auth::user()->id !== 1) {
                $productosQuery->where('empresa_id', Auth::user()->empresa_id);
            }

            // Si hay texto, filtramos; limitamos resultados (por ejemplo 50) para rendimiento
            $productosQuery = $productosQuery->when(!empty($this->producto_nombre), function ($query) {
                $nombre = $this->producto_nombre;
                $query->where(function ($q) use ($nombre) {
                    $q->where('sku', 'LIKE', "%{$nombre}%")
                      ->orWhere('nombre', 'LIKE', "%{$nombre}%")
                      ->orWhere('codigo', 'LIKE', "%{$nombre}%")
                      ->orWhereRaw("CONCAT(sku, ' - ', nombre) LIKE ?", ["%{$nombre}%"]);
                });
            });

            $productos = $productosQuery
                ->limit(50)
                ->get()
                ->mapWithKeys(fn($p) => [$p->id => "{$p->sku} - {$p->nombre}"]);

        } catch (QueryException $e) {
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

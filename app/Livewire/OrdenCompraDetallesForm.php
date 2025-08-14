<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Productos;
use App\Models\UnidadDeMedidas;
use App\Models\CategoriaProducto;
use App\Models\SubcategoriaProducto;
use App\Models\OrdenCompras;
use App\Models\OrdenComprasDetalle;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Reactive;

class OrdenCompraDetallesForm extends Component implements HasForms
{
    use InteractsWithForms;

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
    #[Reactive]
    public $subcategorias = [];

    protected $listeners = ['updateFormState' => 'updateFormState'];

    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()->schema($this->getFormSchema()),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('producto_nombre')
    ->label('Producto')
    ->options(function () {
        $user = Auth::user();

        $productos = $user->hasRole('root')
            ? Productos::all()
            : Productos::where('empresa_id', $user->empresa_id)->get();

        // Devuelve un array ['ID' => 'Nombre | SKU | Código']
        return $productos->mapWithKeys(function ($producto) {
            return [$producto->id => $producto->nombre . ' | ' . $producto->sku . ' | ' . $producto->codigo];
        })->toArray();
    })
    ->searchable()
    ->required()
    ->preload()
    ->afterStateUpdated(function ($state, $set) {
        // Guarda el ID del producto seleccionado
        $this->producto_id = $state;
    }),

               
            Forms\Components\TextInput::make('cantidad')
                ->label('Cantidad')
                ->required()
                ->numeric()
                ->minValue(1),
            Forms\Components\TextInput::make('precio')
                ->label('Precio (Lps)')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step(0.01),
        ];
    }

    public function mount($ordenId = null)
    {
        $this->ordenId = $ordenId;
        if ($this->ordenId) {
            $orden = OrdenCompras::with('detalles.producto')->find($this->ordenId);
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

    public function updateProductoId($nombre, callable $set)
    {
        if (empty($nombre)) {
            $this->producto_id = null;
            $set('producto_nombre', '');
            return;
        }
        try {
            $query = Productos::query();
            if (Auth::user()->id !== 1 && $this->formState['empresa_id']) {
                $query->where('empresa_id', $this->formState['empresa_id']);
            }
            $producto = $query
                ->where(function ($query) use ($nombre) {
                    $query->where('sku', 'LIKE', "%{$nombre}%")
                          ->orWhere('nombre', 'LIKE', "%{$nombre}%")
                          ->orWhere('codigo', 'LIKE', "%{$nombre}%")
                          ->orWhereRaw("CONCAT(sku, ' - ', nombre) LIKE ?", ["%{$nombre}%"]);
                })
                ->first();
            if ($producto) {
                $this->producto_id = $producto->id;
                $this->producto_nombre = $producto->sku . ' - ' . $producto->nombre;
                $set('producto_nombre', $this->producto_nombre);
            } else {
                $this->producto_id = null;
                Notification::make()
                    ->title('Advertencia')
                    ->body('El producto no se encontró. Puede crear uno nuevo usando el botón "+".')
                    ->warning()
                    ->send();
            }
        } catch (QueryException $e) {
            Log::error('Error en updateProductoId: ' . $e->getMessage());
            $this->producto_id = null;
            $set('producto_nombre', '');
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al buscar el producto.')
                ->danger()
                ->send();
        }
    }

    public function updateSubcategorias($categoriaId, callable $set)
    {
        $this->subcategorias = [];
        if ($categoriaId) {
            $this->subcategorias = SubcategoriaProducto::where('categoria_id', $categoriaId)
                ->where('empresa_id', $this->formState['empresa_id'] ?? Auth::user()->empresa_id)
                ->pluck('nombre', 'id')
                ->toArray();
            $set('subcategoria_id', null);
        }
    }

    public function addProducto()
    {
        $this->form->validate([
            'producto_nombre' => 'nullable',
            'cantidad' => 'required|numeric|min:1',
            'precio' => 'required|numeric|min:0',
        ]);

        if (!$this->producto_id) {
            Notification::make()
                ->title('Error')
                ->body('Por favor, selecciona un producto válido antes de añadir.')
                ->danger()
                ->send();
            return;
        }

        try {
            $producto = Productos::where('id', $this->producto_id)
                ->when(Auth::user()->id !== 1 && $this->formState['empresa_id'], fn ($query) => $query->where('empresa_id', $this->formState['empresa_id']))
                ->first();
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
                Notification::make()
                    ->title('Éxito')
                    ->body('Producto actualizado correctamente.')
                    ->success()
                    ->send();
            } else {
                $indexExistente = collect($this->detalles)->search(fn ($item) => $item['producto_id'] == $producto->id);
                if ($indexExistente !== false) {
                    $this->detalles[$indexExistente]['cantidad'] += $this->cantidad;
                    $this->detalles[$indexExistente]['precio'] = $this->precio;
                    Notification::make()
                        ->title('Éxito')
                        ->body('Cantidad del producto actualizada.')
                        ->success()
                        ->send();
                } else {
                    $this->detalles[] = [
                        'producto_id' => $producto->id,
                        'nombre_producto' => $producto->nombre,
                        'cantidad' => $this->cantidad,
                        'precio' => $this->precio,
                    ];
                    Notification::make()
                        ->title('Éxito')
                        ->body('Producto añadido a la tabla.')
                        ->success()
                        ->send();
                }
            }
            session()->put('detalles_orden', $this->detalles);
            $this->reset(['producto_id', 'producto_nombre', 'cantidad', 'precio']);
            $this->cantidad = 1;
            $this->precio = 0;
            $this->form->fill(['cantidad' => 1, 'precio' => 0]);
            $this->dispatch('productoAdded');
        } catch (QueryException $e) {
            Log::error('Error en addProducto: ' . $e->getMessage());
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al agregar el producto.')
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
        $this->form->fill([
            'producto_nombre' => $this->producto_nombre,
            'cantidad' => $this->cantidad,
            'precio' => $this->precio,
        ]);
    }

    public function removeDetalle($index)
    {
        $detalle = $this->detalles[$index] ?? null;
        if (isset($detalle['detalle_id'])) {
            try {
                OrdenComprasDetalle::find($detalle['detalle_id'])?->delete();
            } catch (QueryException $e) {
                Log::error('Error en removeDetalle: ' . $e->getMessage());
                Notification::make()
                    ->title('Error')
                    ->body('Ocurrió un error al eliminar el detalle.')
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

    public function crearOrden()
    {
        if (empty($this->detalles)) {
            Notification::make()
                ->title('Error')
                ->body('Debes añadir al menos un producto a la orden.')
                ->danger()
                ->send();
            return;
        }

        if (empty($this->formState['tipo_orden_compra_id']) ||
            empty($this->formState['proveedor_id']) ||
            empty($this->formState['empresa_id']) ||
            empty($this->formState['fecha_realizada'])) {
            Notification::make()
                ->title('Error')
                ->body('Por favor, completa todos los campos obligatorios de la orden.')
                ->danger()
                ->send();
            return;
        }

        try {
            \DB::beginTransaction();
            $orden = OrdenCompras::create([
                'tipo_orden_compra_id' => $this->formState['tipo_orden_compra_id'],
                'proveedor_id' => $this->formState['proveedor_id'],
                'empresa_id' => $this->formState['empresa_id'],
                'fecha_realizada' => $this->formState['fecha_realizada'],
                'descripcion' => $this->formState['descripcion'],
                'created_by' => Auth::user()->id,
            ]);

            foreach ($this->detalles as $detalle) {
                OrdenComprasDetalle::create([
                    'orden_compra_id' => $orden->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $detalle['precio'],
                ]);
            }

            \DB::commit();
            session()->forget('detalles_orden');
            $this->detalles = [];
            $this->ordenId = $orden->id;

            Notification::make()
                ->title('Éxito')
                ->body('La orden de compra ha sido creada con éxito.')
                ->success()
                ->send();

            $this->dispatch('ordenCreada', $orden->id);
            return redirect()->route('ordenes-compras.index'); // Ajusta la ruta según tu aplicación
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error al crear orden: ' . $e->getMessage());
            Notification::make()
                ->title('Error')
                ->body('No se pudo crear la orden de compra.')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        $isBasicInfoComplete = !empty($this->formState['tipo_orden_compra_id']) &&
                               !empty($this->formState['proveedor_id']) &&
                               !empty($this->formState['empresa_id']) &&
                               !empty($this->formState['fecha_realizada']);
        try {
            $productos = Productos::query()
                ->when(Auth::user()->id !== 1 && $this->formState['empresa_id'], fn ($query) => $query->where('empresa_id', $this->formState['empresa_id']))
                ->when(!empty($this->producto_nombre), function ($query) {
                    $nombre = trim($this->producto_nombre);
                    $query->where(function ($q) use ($nombre) {
                        $q->where('sku', 'LIKE', "%{$nombre}%")
                          ->orWhere('nombre', 'LIKE', "%{$nombre}%")
                          ->orWhere('codigo', 'LIKE', "%{$nombre}%")
                          ->orWhereRaw("CONCAT(sku, ' - ', nombre) LIKE ?", ["%{$nombre}%"]);
                    });
                })
                ->get()
                ->mapWithKeys(fn ($p) => [$p->id => "{$p->sku} - {$p->nombre}"]);
        } catch (QueryException $e) {
            Log::error('Error en render: ' . $e->getMessage());
            $productos = collect([]);
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al cargar los productos.')
                ->danger()
                ->send();
        }
        return view('livewire.orden-compra-detalles-form', [
            'productos' => $productos,
            'isBasicInfoComplete' => $isBasicInfoComplete,
            'subcategorias' => $this->subcategorias,
        ]);
    }
}
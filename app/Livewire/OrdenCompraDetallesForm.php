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
            Forms\Components\TextInput::make('producto_nombre')
                ->label('Producto')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set) {
                    $this->updateProductoId($state, $set);
                })
                ->datalist(function () {
                    $query = Productos::query();
                    if (Auth::user()->id !== 1 && $this->formState['empresa_id']) {
                        $query->where('empresa_id', $this->formState['empresa_id']);
                    }
                    return $query->pluck('nombre', 'id')->toArray();
                })
                ->suffixAction(
                    Forms\Components\Actions\Action::make('createProducto')
                        ->label('Agregar')
                        ->icon('heroicon-o-plus')
                        ->tooltip('Agregar un nuevo producto')
                        ->modalHeading('Crear Nuevo Producto')
                        ->form([
                            Forms\Components\TextInput::make('nombre')
                                ->label('Nombre del producto')
                                ->required()
                                ->maxLength(100)
                                ->unique(ignorable: fn ($record) => $record, table: Productos::class),
                            Forms\Components\Textarea::make('descripcion_corta')
                                ->label('Descripción corta')
                                ->rows(2)
                                ->maxLength(255)
                                ->placeholder('Ejemplo: Papel bond de alta calidad'),
                            Forms\Components\Textarea::make('descripcion')
                                ->label('Descripción larga')
                                ->rows(4)
                                ->maxLength(255)
                                ->placeholder('Ejemplo: Papel bond de 80 gramos, ideal para impresión'),
                            Forms\Components\TextInput::make('color')
                                ->label('Color')
                                ->required()
                                ->maxLength(50)
                                ->placeholder('Ejemplo: Blanco'),
                            Forms\Components\Select::make('unidad_de_medida_id')
                                ->label('Unidad de medida')
                                ->options(UnidadDeMedidas::pluck('nombre', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->placeholder('Seleccione una unidad')
                                ->suffixAction(
                                    Forms\Components\Actions\Action::make('createUnidadDeMedida')
                                        ->label('Agregar')
                                        ->icon('heroicon-o-plus')
                                        ->tooltip('Agregar una nueva unidad de medida')
                                        ->modalHeading('Crear Nueva Unidad de Medida')
                                        ->form([
                                            Forms\Components\TextInput::make('nombre')
                                                ->label('Nombre de la Unidad')
                                                ->required()
                                                ->maxLength(100),
                                            Forms\Components\TextInput::make('abreviacion')
                                                ->label('Abreviación')
                                                ->required()
                                                ->maxLength(10),
                                            Forms\Components\Select::make('categoria_id')
                                                ->label('Categoría')
                                                ->options(
                                                    CategoriaProducto::where('empresa_id', $this->formState['empresa_id'] ?? Auth::user()->empresa_id)
                                                        ->pluck('nombre', 'id')
                                                )
                                                ->searchable()
                                                ->required(),
                                        ])
                                        ->action(function (array $data, callable $set) {
                                            $newUnidad = UnidadDeMedidas::create($data);
                                            Notification::make()
                                                ->title('Unidad de Medida Creada')
                                                ->body("La unidad de medida {$newUnidad->nombre} ha sido creada con éxito.")
                                                ->success()
                                                ->send();
                                            $set('unidad_de_medida_id', $newUnidad->id);
                                        })
                                        ->modal()
                                ),
                            Forms\Components\Select::make('categoria_id')
                                ->label('Categoría')
                                ->options(
                                    CategoriaProducto::where('empresa_id', $this->formState['empresa_id'] ?? Auth::user()->empresa_id)
                                        ->pluck('nombre', 'id')
                                )
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $this->updateSubcategorias($state, $set);
                                })
                                ->placeholder('Seleccione una categoría')
                                ->suffixAction(
                                    Forms\Components\Actions\Action::make('createCategoria')
                                        ->label('Agregar')
                                        ->icon('heroicon-o-plus')
                                        ->tooltip('Agregar una nueva categoría')
                                        ->modalHeading('Crear Nueva Categoría')
                                        ->form([
                                            Forms\Components\TextInput::make('nombre')
                                                ->label('Nombre de la Categoría')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ejemplo: Ropa, Electrónica')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $empresaId = $this->formState['empresa_id'] ?? Auth::user()->empresa_id;
                                                    $existing = CategoriaProducto::where('nombre', $state)
                                                        ->where('empresa_id', $empresaId)
                                                        ->first();
                                                    if ($existing) {
                                                        Notification::make()
                                                            ->title('Categoría ya existente')
                                                            ->body('La categoría "' . $state . '" ya existe.')
                                                            ->danger()
                                                            ->send();
                                                        $set('nombre', null);
                                                    }
                                                }),
                                            Forms\Components\Repeater::make('subcategorias')
                                                ->label('Subcategorías')
                                                ->schema([
                                                    Forms\Components\TextInput::make('nombre')
                                                        ->label('Nombre de la Subcategoría')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->placeholder('Ejemplo: Camisetas, Laptops'),
                                                ])
                                                ->required()
                                                ->minItems(1)
                                                ->addActionLabel('Añadir Subcategoría')
                                                ->deleteAction(
                                                    fn ($action) => $action->requiresConfirmation()->label('Eliminar Subcategoría')
                                                ),
                                        ])
                                        ->action(function (array $data, callable $set) {
                                            $empresaId = $this->formState['empresa_id'] ?? Auth::user()->empresa_id;
                                            try {
                                                \DB::beginTransaction();
                                                $newCategoria = CategoriaProducto::create([
                                                    'nombre' => $data['nombre'],
                                                    'empresa_id' => $empresaId,
                                                ]);
                                                foreach ($data['subcategorias'] as $subcategoria) {
                                                    SubcategoriaProducto::create([
                                                        'nombre' => $subcategoria['nombre'],
                                                        'categoria_id' => $newCategoria->id,
                                                        'empresa_id' => $empresaId,
                                                    ]);
                                                }
                                                \DB::commit();
                                                $set('categoria_id', $newCategoria->id);
                                                $this->updateSubcategorias($newCategoria->id, $set);
                                                Notification::make()
                                                    ->title('Categoría Creada')
                                                    ->body("La categoría {$newCategoria->nombre} ha sido creada con éxito.")
                                                    ->success()
                                                    ->send();
                                            } catch (\Exception $e) {
                                                \DB::rollBack();
                                                Log::error('Error al crear categoría: ' . $e->getMessage());
                                                Notification::make()
                                                    ->title('Error')
                                                    ->body('No se pudo crear la categoría.')
                                                    ->danger()
                                                    ->send();
                                            }
                                        })
                                        ->modal()
                                ),
                            Forms\Components\Select::make('subcategoria_id')
                                ->label('Subcategoría')
                                ->options($this->subcategorias)
                                ->required()
                                ->searchable()
                                ->preload()
                                ->disabled(fn ($get) => !$get('categoria_id')),
                            Forms\Components\TextInput::make('sku')
                                ->label('SKU')
                                ->maxLength(100)
                                ->default(fn () => strtoupper(Str::random(3) . '-' . rand(10000, 99999)))
                                ->unique(ignorable: fn ($record) => $record, table: Productos::class),
                            Forms\Components\TextInput::make('codigo')
                                ->label('Código de barras')
                                ->maxLength(100)
                                ->default(fn () => Str::random(8))
                                ->unique(ignorable: fn ($record) => $record, table: Productos::class),
                            Forms\Components\TextInput::make('isv')
                                ->label('ISV (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(20)
                                ->default(0),
                            Forms\Components\Hidden::make('empresa_id')
                                ->default(fn () => $this->formState['empresa_id'] ?? Auth::user()->empresa_id)
                                ->required(),
                        ])
                        ->action(function (array $data, callable $set) {
                            Log::info('Botón + clicado para crear producto', ['data' => $data]);
                            try {
                                $newProducto = Productos::create($data);
                                $this->producto_id = $newProducto->id;
                                $this->producto_nombre = $newProducto->sku . ' - ' . $newProducto->nombre;
                                $set('producto_nombre', $this->producto_nombre);
                                Notification::make()
                                    ->title('Producto Creado')
                                    ->body("El producto {$newProducto->nombre} ha sido creado con éxito.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Log::error('Error al crear producto: ' . $e->getMessage());
                                Notification::make()
                                    ->title('Error')
                                    ->body('No se pudo crear el producto: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->modalWidth('lg')
                        ->modal()
                ),
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
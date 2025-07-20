<?php

namespace App\Filament\Resources\FacturaResource\Pages;

use App\Filament\Resources\FacturaResource;
use Filament\Resources\Pages\Page;
use App\Models\Factura;
use App\Models\InventarioProductos;
use App\Models\detalle_factura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;
use App\Filament\Resources\FacturaResource\Pages\EditFactura;
use App\Filament\Resources\FacturaResource\Pages\ViewFactura;
use App\Filament\Resources\FacturaResource\Pages\ListFacturas;
use App\Models\Cliente;
use App\Models\Empleado;


class GenerarFactura extends Page
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = FacturaResource::class;
    protected static string $view = 'filament.resources.factura-resource.pages.generar-factura';
    protected static ?string $title = 'Generar Orden de Venta';

    
    public ?array $data = [];
    public array $lineasVenta = [];
    public float $subtotal = 0;
    public float $impuestos = 0;
    public float $total = 0;
    const TASA_ISV = 0.15;

    public function mount(): void
    {
        $this->form->fill([
            'tipo_precio' => 'precio_detalle',
            'cantidad_busqueda' => 1,
        ]);
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'md' => 2])->schema([
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search): array {
                            $query = \App\Models\Cliente::query();

                    if (auth()->user()->hasRole('root')) {
                        $query->withoutGlobalScopes();
                    }

                    // Buscar por nombre o por DNI en la relación persona
                    return $query
                        ->whereHas('persona', function ($q) use ($search) {
                            $q->where('dni', 'like', "%{$search}%")
                            ->orWhere('primer_nombre', 'like', "%{$search}%")
                            ->orWhere('segundo_nombre', 'like', "%{$search}%")
                            ->orWhere('primer_apellido', 'like', "%{$search}%")
                            ->orWhere('segundo_apellido', 'like', "%{$search}%");
                        })
                        ->with('persona')
                        ->limit(10)
                        ->get()
                        ->mapWithKeys(function ($cliente) {
                            $persona = $cliente->persona;
                            if (!$persona) return [];
                            $nombreCompleto = trim(
                                "{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"
                            );
                            return [
                                $cliente->id => "{$nombreCompleto} ({$persona->dni})"
                            ];
                        })
                        ->toArray();
                })
                ->getOptionLabelUsing(function ($value) {
                    $cliente = \App\Models\Cliente::with('persona')->find($value);
                    if (!$cliente || !$cliente->persona) return null;
                    $persona = $cliente->persona;
                    $nombreCompleto = trim(
                        "{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"
                    );
                    return "{$nombreCompleto} ({$persona->dni})";
                })
                ->required()
                ->placeholder('Busque por nombre o DNI del cliente...'),
                    Select::make('tipo_precio')
                        ->label('Tipo de Precio a Aplicar')
                        ->options([
                            'precio_detalle' => 'Precio Detalle',
                            'precio_mayorista' => 'Precio Mayorista',
                            'precio_promocion' => 'Precio de Promoción',
                        ])
                        ->live() // Hace que el campo sea reactivo
                        ->required(),
                ]),
                Grid::make(['default' => 1, 'md' => 3])
                    ->extraAttributes(['class' => 'items-end']) 
                    ->schema([
                        TextInput::make('sku_busqueda')
                            ->label('Buscar por Código de Barras / SKU')
                            ->placeholder('Escanee o ingrese el código...')
                            ->autofocus()
                            ->live(debounce: 500)
                            ->extraAttributes(['wire:keydown.enter.prevent' => 'agregarProducto']),
                        TextInput::make('cantidad_busqueda')
                            ->label('Cantidad')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('agregar')
                                ->label('Agregar Producto')
                                ->icon('heroicon-o-plus-circle')
                                ->action('agregarProducto')
                        ]),
                ]),
            ])
            ->statePath('data');
    }

    public function updatedDataTipoPrecio($value): void
    {
        if (empty($this->lineasVenta)) {
            return;
        }

        $tipoPrecioSeleccionado = $value;
        $nuevasLineas = [];

        foreach ($this->lineasVenta as $productoId => $linea) {
            $inventarioProducto = InventarioProductos::find($linea['inventario_id']);
            if ($inventarioProducto) {
                $linea['precio_unitario'] = $inventarioProducto->{$tipoPrecioSeleccionado};
                $linea['tipo_precio_label'] = $this->getTipoPrecioLabel($tipoPrecioSeleccionado);
            }
            $nuevasLineas[$productoId] = $linea;
        }

        $this->lineasVenta = $nuevasLineas;
        $this->calcularTotales();
    }
    
    public function updatedDataSkuBusqueda($value): void
    {
        if (!empty($value)) {
            $this->agregarProducto();
        }
    }

    public function agregarProducto(): void
    {
        $data = $this->form->getState();
        $sku = $data['sku_busqueda'] ?? null;
        $cantidad = (int) ($data['cantidad_busqueda'] ?? 1);
        $tipoPrecio = $data['tipo_precio'];

        if (empty($sku)) return;

        $query = InventarioProductos::query();
        if (auth()->user()->hasRole('root')) {
            $query->withoutGlobalScopes();
        }

        $inventarioProducto = $query->whereHas('producto', fn($q) => $q->where('sku', $sku)->orWhere('codigo', $sku))
            ->with('producto')->first();

        if (!$inventarioProducto) {
            Notification::make()->danger()->title('Producto no encontrado')->send();
            return;
        }

        if ($inventarioProducto->cantidad < $cantidad) {
            Notification::make()->warning()->title('Stock Insuficiente')->body("Solo hay {$inventarioProducto->cantidad} unidades.")->send();
            return;
        }

        $productoId = $inventarioProducto->id;

        if (isset($this->lineasVenta[$productoId])) {
            $this->lineasVenta[$productoId]['cantidad'] += $cantidad;
        } else {
            $this->lineasVenta[$productoId] = [
                'inventario_id' => $inventarioProducto->id,
                'nombre' => $inventarioProducto->producto->nombre,
                'sku' => $inventarioProducto->producto->sku,
                'precio_unitario' => $inventarioProducto->{$tipoPrecio},
                'cantidad' => $cantidad,
                'tipo_precio_label' => $this->getTipoPrecioLabel($tipoPrecio),
            ];
        }

        $this->form->fill([
            'sku_busqueda' => '',
            'cantidad_busqueda' => 1,
            'cliente_id' => $data['cliente_id'],
            'tipo_precio' => $data['tipo_precio'],
        ]);
        
        $this->calcularTotales();
    }

    private function getTipoPrecioLabel(string $tipoPrecio): string
    {
        return match ($tipoPrecio) {
            'precio_mayorista' => 'Mayorista',
            'precio_promocion' => 'Promoción',
            default => 'Detalle',
        };
    }

    public function eliminarProducto(int $productoId): void
    {
        unset($this->lineasVenta[$productoId]);
        $this->calcularTotales();
    }

    public function calcularTotales(): void
    {
        $this->subtotal = 0;
        foreach ($this->lineasVenta as $linea) {
            $this->subtotal += $linea['precio_unitario'] * $linea['cantidad'];
        }
        $this->impuestos = $this->subtotal * self::TASA_ISV;
        $this->total = $this->subtotal + $this->impuestos;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Finalizar y Generar Factura')
                ->color('success')
                ->icon('heroicon-o-document-check')
                ->requiresConfirmation()
                ->action('submit')
                ->disabled(empty($this->lineasVenta)),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        if (empty($this->lineasVenta) || empty($data['cliente_id'])) {
            Notification::make()->danger()->title('Faltan Datos')->body('Debe seleccionar un cliente y agregar productos.')->send();
            return;
        }

        try {
            DB::transaction(function () use ($data) {
                $cliente = auth()->user()->hasRole('root') 
                    ? Cliente::withoutGlobalScopes()->find($data['cliente_id']) 
                    : Cliente::find($data['cliente_id']);

                if (!$cliente) {
                    throw new \Exception('Cliente no encontrado.');
                }

                $empleado = auth()->user()->empleado;

                // 2. Si el usuario no tiene un empleado (es root), busca el primer empleado de la BD como respaldo.
                if (!$empleado) {
                    $empleado = Empleado::first();
                }

                // 3. Si AÚN no hay empleado (la tabla está vacía), lanza un error claro.
                if (!$empleado) {
                    throw new \Exception('No se encontró un empleado para asignar a la factura. Verifique que exista al menos un empleado en el sistema.');
                }

                $factura = Factura::create([
                    'cliente_id' => $cliente->id,
                    'empleado_id' => $empleado->id,
                    'empresa_id' => $cliente->empresa_id,
                    'fecha_factura' => now(),
                    'estado' => 'Pendiente',
                    'subtotal' => $this->subtotal,
                    'impuestos' => $this->impuestos,
                    'total' => $this->total,
                ]);

                foreach ($this->lineasVenta as $linea) {
                    detalle_factura::create([
                        'factura_id' => $factura->id,
                        'producto_id' => $linea['inventario_id'],
                        'cantidad' => $linea['cantidad'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'sub_total' => $linea['cantidad'] * $linea['precio_unitario'],
                    ]);
                    
                    $inventario = auth()->user()->hasRole('root')
                        ? InventarioProductos::withoutGlobalScopes()->find($linea['inventario_id'])
                        : InventarioProductos::find($linea['inventario_id']);
                    
                    $inventario?->decrement('cantidad', $linea['cantidad']);
                }
                
                Notification::make()->success()->title('¡Factura Generada!')->send();
                redirect(FacturaResource::getUrl('view', ['record' => $factura]));
            });
        } catch (\Exception $e) {
            Notification::make()->danger()->title('Error al generar la factura')->body($e->getMessage())->send();
        }
    }
}
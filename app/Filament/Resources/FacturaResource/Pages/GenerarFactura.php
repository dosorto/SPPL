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

    public function mount(): void
    {
        $consumidorFinal = Cliente::whereHas('persona', function ($query) {
            $query->where('dni', '0000000000000');
        })->first();

        // 2. Se rellenan los campos del formulario por defecto.
        $this->form->fill([
            'tipo_precio' => 'precio_detalle',
            'cantidad_busqueda' => 1,
            // Si se encontró al consumidor final, se usa su ID. Si no, se deja en blanco.
            'cliente_id' => $consumidorFinal ? $consumidorFinal->id : null,
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
                        ->label('Aplicar Precio Global')
                        ->options([
                            'precio_detalle' => 'Precio Detalle',
                            'precio_mayorista' => 'Precio Mayorista',
                            'precio_promocion' => 'Precio de Promoción',
                        ])
                        ->live() 
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
                $linea['tipo_precio_key'] = $tipoPrecioSeleccionado;
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
                'tipo_precio_key' => $tipoPrecio,
                'tipo_precio_label' => $this->getTipoPrecioLabel($tipoPrecio),
                'isv_producto' => $inventarioProducto->producto->isv ?? 0,
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

    public function actualizarTipoPrecioLinea(int $productoId, string $nuevoTipoPrecio): void
    {
        if (isset($this->lineasVenta[$productoId])) {
            $inventarioProducto = InventarioProductos::find($this->lineasVenta[$productoId]['inventario_id']);
            if ($inventarioProducto) {
                // Actualizamos los datos de la línea con el nuevo precio
                $this->lineasVenta[$productoId]['precio_unitario'] = $inventarioProducto->{$nuevoTipoPrecio};
                $this->lineasVenta[$productoId]['tipo_precio_key'] = $nuevoTipoPrecio;
                $this->lineasVenta[$productoId]['tipo_precio_label'] = $this->getTipoPrecioLabel($nuevoTipoPrecio);
                
                // Recalculamos los totales generales
                $this->calcularTotales();
            }
        }
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
        // --- INICIO DE LA LÓGICA DE CÁLCULO MODIFICADA ---
        $this->subtotal = 0;
        $this->impuestos = 0; // Se resetea el total de impuestos

        foreach ($this->lineasVenta as $linea) {
            // Se calcula el subtotal de la línea (precio * cantidad)
            $subtotalLinea = $linea['precio_unitario'] * $linea['cantidad'];
            
            // Se calcula el impuesto para ESTA línea específica
            // Se asume que el ISV en la BD está como un porcentaje (ej: 15 para 15%)
            $impuestoLinea = $subtotalLinea * ($linea['isv_producto'] / 100);

            // Se suma al subtotal y a los impuestos generales de la factura
            $this->subtotal += $subtotalLinea;
            $this->impuestos += $impuestoLinea;
        }

        // El total general ahora es la suma del subtotal + los impuestos calculados
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

                // 2. Validar que el usuario actual ESTÉ enlazado a un empleado.
                //    Si no lo está, no puede generar facturas. Es una regla de negocio importante.
                if (!$empleado) {
                    throw new \Exception('El usuario actual no está asociado a ningún empleado. Contacte al administrador del sistema.');
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
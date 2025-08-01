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
use App\Models\DetalleFactura;
use App\Models\Empleado;
use Filament\Forms\Components\Html;
use Illuminate\Support\Facades\Session;
use App\Filament\Pages\CierreCaja;
use App\Models\MetodoPago;
use Illuminate\Support\Str;
use App\Filament\Pages\AperturaCaja; 
use App\Models\CajaApertura; 
use Illuminate\Support\Facades\Auth; 


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
    public ?string $categoriaClienteNombre = null;

    public function mount(?int $record = null): void
    {
        // 0) Validar que haya una caja abierta
        $aperturaId  = session('apertura_id');
        $userId      = Auth::id();
        $cajaAbierta = CajaApertura::where('id', $aperturaId)
            ->where('user_id', $userId)
            ->where('estado', 'ABIERTA')
            ->exists();

        if (! $cajaAbierta) {
            session()->forget('apertura_id');
            Notification::make()
                ->title('Acceso Denegado')
                ->body('No tienes una caja activa para facturar. Por favor, abre una caja primero.')
                ->danger()
                ->send();
            $this->redirect(AperturaCaja::getUrl());
            return;
        }

        if ($record) {
            // --- EDICIÓN DE FACTURA PENDIENTE ---
            $factura = Factura::with('detalles.producto.producto')
                ->findOrFail($record);

            // (1) relleno el formulario con los datos del cliente
            $this->form->fill([
                'cliente_id'        => $factura->cliente_id,
                'tipo_precio'       => 'precio_detalle',
                'cantidad_busqueda' => 1,
                'usar_cai'          => true,
            ]);

            // (2) reconstruyo las líneas de venta desde los detalles
            $this->lineasVenta = $factura->detalles
                ->mapWithKeys(fn($det) => [
                    $det->producto_id => [
                        'inventario_id'      => $det->producto_id,
                        'sku'                => $det->producto->producto->sku,
                        'nombre'             => $det->producto->producto->nombre,
                        'precio_unitario'    => $det->precio_unitario,
                        'cantidad'           => $det->cantidad,
                        'isv_producto'       => $det->isv_aplicado,
                        'descuento_aplicado' => $det->descuento_aplicado,
                        'tipo_precio_key'    => 'precio_detalle',
                        'tipo_precio_label'  => 'Detalle',
                    ],
                ])->toArray();

            $this->calcularTotales();

            // (3) dejo el id en sesión para saber que sigo en modo edición
            session(['factura_pendiente_id' => $record]);
        } else {
            // --- CREACIÓN DE UNA NUEVA ORDEN (NO CARGO NADA ANTIGUO) ---
            session()->forget('factura_pendiente_id');

            $consumidorFinal = Cliente::whereHas('persona', fn($q) =>
                $q->where('dni', '0000000000000')
            )->first();

            // 1) valores por defecto del formulario
            $this->form->fill([
                'cliente_id'        => $consumidorFinal->id ?? null,
                'tipo_precio'       => 'precio_detalle',
                'cantidad_busqueda' => 1,
                'usar_cai'          => true,
            ]);

            // 2) limpio cualquier línea previa
            $this->lineasVenta = [];
            $this->subtotal    = 0;
            $this->impuestos   = 0;
            $this->total       = 0;
        }
    }



    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(12)->schema([
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search): array {
                            $query = \App\Models\Cliente::query();

                            if (auth()->user()->hasRole('root')) {
                                $query->withoutGlobalScopes();
                            }

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
                                    $nombreCompleto = trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}");
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
                            $nombreCompleto = trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}");
                            return "{$nombreCompleto} ({$persona->dni})";
                        })
                        ->required()
                        ->placeholder('Busque por nombre o DNI del cliente...')
                        ->columnSpan(5),

                    

                    Select::make('tipo_precio')
                        ->label('Aplicar Precio Global')
                        ->options([
                            'precio_detalle' => 'Precio Detalle',
                            'precio_mayorista' => 'Precio Mayorista',
                            'precio_promocion' => 'Precio de Promoción',
                        ])
                        ->live()
                        ->required()
                        ->columnSpan(5),

                ]),

                Grid::make(12)
                    ->extraAttributes(['class' => 'items-end'])
                    ->schema([
                        TextInput::make('cantidad_busqueda')
                            ->label('Cantidad')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->columnSpan(3),

                        TextInput::make('sku_busqueda')
                            ->label('Buscar por Código de Barras / SKU')
                            ->placeholder('Escanee o ingrese el código...')
                            ->autofocus()
                            ->live(debounce: 500)
                            ->extraAttributes(['wire:keydown.enter.prevent' => 'agregarProducto'])
                            ->columnSpan(6),

                        Forms\Components\Group::make([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('agregar')
                                    ->label('Agregar Producto')
                                    ->icon('heroicon-o-plus-circle')
                                    ->action('agregarProducto')
                                    ->extraAttributes(['class' => 'w-full h-full']),
                            ]),
                        ])
                        ->extraAttributes(['class' => 'mt-6'])
                        ->columnSpan(['md' => 2.5]),
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
        $data = $this->form->getState();

        $cliente = Cliente::with('categoriaCliente.categoriasProductos')->find($data['cliente_id']);

        $nuevasLineas = [];

        foreach ($this->lineasVenta as $productoId => $linea) {
            $inventarioProducto = InventarioProductos::with('producto')->find($linea['inventario_id']);
            if ($inventarioProducto) {
                $precioOriginal = $inventarioProducto->{$tipoPrecioSeleccionado};
                $descuento = 0;

                if ($cliente && $cliente->categoriaCliente) {
                    $categoriaRelacionada = $cliente->categoriaCliente
                        ->categoriasProductos()
                        ->where('categoria_producto_id', $inventarioProducto->producto->categoria_id)
                        ->wherePivot('activo', true)
                        ->first();

                    if ($categoriaRelacionada) {
                        $descuento = $categoriaRelacionada->pivot->descuento_porcentaje ?? 0;
                    }
                }

                $precioConDescuento = round($precioOriginal * (1 - ($descuento / 100)), 2);

                $linea['precio_unitario'] = $precioConDescuento;
                $linea['tipo_precio_key'] = $tipoPrecioSeleccionado;
                $linea['tipo_precio_label'] = $this->getTipoPrecioLabel($tipoPrecioSeleccionado);
                $linea['descuento_aplicado'] = $descuento;
            }

            $nuevasLineas[$productoId] = $linea;
        }

        $this->lineasVenta = $nuevasLineas;
        $this->dispatch('refresh');
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

        $cliente = Cliente::with('categoriaCliente.categoriasProductos')->find($data['cliente_id']);
        $descuento = 0;

        if ($cliente && $cliente->categoriaCliente) {
            $categoriaRelacionada = $cliente->categoriaCliente
                ->categoriasProductos()
                ->where('categoria_producto_id', $inventarioProducto->producto->categoria_id)
                ->wherePivot('activo', true)
                ->first();

            if ($categoriaRelacionada) {
                $descuento = $categoriaRelacionada->pivot->descuento_porcentaje ?? 0;
            }
        }

        $precioOriginal = $inventarioProducto->{$tipoPrecio};
        $precioConDescuento = round($precioOriginal * (1 - ($descuento / 100)), 2);

        if (isset($this->lineasVenta[$productoId])) {
            $this->lineasVenta[$productoId]['cantidad'] += $cantidad;
        } else {
            $this->lineasVenta[$productoId] = [
                'inventario_id' => $inventarioProducto->id,
                'nombre' => $inventarioProducto->producto->nombre,
                'sku' => $inventarioProducto->producto->sku,
                'precio_unitario' => $precioConDescuento,
                'cantidad' => $cantidad,
                'tipo_precio_key' => $tipoPrecio,
                'tipo_precio_label' => $this->getTipoPrecioLabel($tipoPrecio),
                'isv_producto' => $inventarioProducto->producto->isv ?? 0,
                'descuento_aplicado' => $descuento,
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

        // --- FIN DE LA LÓGICA DE CÁLCULO MODIFICADA ---
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Agregar Métodos de Pago')
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

        // 1) Validación básica: cliente y líneas de venta
        if (empty($this->lineasVenta) || empty($data['cliente_id'])) {
            Notification::make()
                ->danger()
                ->title('Faltan Datos')
                ->body('Debe seleccionar un cliente y agregar productos.')
                ->send();

            return;
        }

        try {
            DB::transaction(function () use ($data) {
                // 2) Cargar cliente respetando scopes
                $cliente = auth()->user()->hasRole('root')
                    ? Cliente::withoutGlobalScopes()->find($data['cliente_id'])
                    : Cliente::find($data['cliente_id']);
                if (! $cliente) {
                    throw new \Exception('Cliente no encontrado.');
                }

                // 3) Cargar empleado asociado al usuario
                $empleado = auth()->user()->empleado;
                if (! $empleado) {
                    throw new \Exception('Usuario no asociado a ningún empleado.');
                }

                // 4) Obtener apertura de caja
                $aperturaId = session('apertura_id');

                // 5) Crear la factura en estado "Pendiente" con número temporal
                /** @var Factura $factura */
                $factura = Factura::create([
                    'cliente_id'      => $cliente->id,
                    'empleado_id'     => $empleado->id,
                    'empresa_id'      => $cliente->empresa_id,
                    'fecha_factura'   => now(),
                    'estado'          => 'Pendiente',
                    'subtotal'        => $this->subtotal,
                    'impuestos'       => $this->impuestos,
                    'total'           => $this->total,
                    'numero_factura'  => 'PEND-' . Str::uuid(), // placeholder nunca NULL
                    'cai_id'          => null,
                    'apertura_id'     => $aperturaId,
                ]);

                // 6) Sobrescribir el número con su propio ID
                $factura->update([
                    'numero_factura' => (string) $factura->id,
                ]);

                // 7) Guardar cada línea de detalle y decrementar stock
                foreach ($this->lineasVenta as $linea) {
                    $inventario = auth()->user()->hasRole('root')
                        ? InventarioProductos::withoutGlobalScopes()->find($linea['inventario_id'])
                        : InventarioProductos::find($linea['inventario_id']);
                    $costo = $inventario?->precio_costo ?? 0;

                    DetalleFactura::create([
                        'factura_id'         => $factura->id,
                        'producto_id'        => $linea['inventario_id'],
                        'cantidad'           => $linea['cantidad'],
                        'precio_unitario'    => $linea['precio_unitario'],
                        'descuento_aplicado' => $linea['descuento_aplicado'] ?? 0,
                        'sub_total'          => $linea['cantidad'] * $linea['precio_unitario'],
                        'isv_aplicado'       => $linea['isv_producto'] ?? 0,
                        'costo_unitario'     => $costo,
                        'utilidad_unitaria'  => round($linea['precio_unitario'] - $costo, 2),
                    ]);

                    $inventario?->decrement('cantidad', $linea['cantidad']);
                }

                // 8) Notificar y redirigir al formulario de pagos
                Notification::make()
                    ->success()
                    ->title('Orden creada como pendiente')
                    ->body('Ahora puedes registrar el pago para asignar CAI y número.')
                    ->send();

                redirect(FacturaResource::getUrl('registrar-pago', ['record' => $factura->id]));
            });
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error al generar la factura')
                ->body($e->getMessage())
                ->send();
        }
    }



    public function guardarPendiente(): void
    {
        $data = $this->form->getState();

        if (empty($this->lineasVenta) || empty($data['cliente_id'])) {
            Notification::make()
                ->danger()
                ->title('Faltan Datos')
                ->body('Debe seleccionar un cliente y agregar productos.')
                ->send();

            return;
        }

        try {
            DB::transaction(function () use ($data) {
                // 1) ¿Estoy re-editando una factura pendiente?
                $pendienteId = session('factura_pendiente_id');

                if ($pendienteId) {
                    // 1.a) Actualizo totales en la factura existente
                    $factura = Factura::findOrFail($pendienteId);
                    $factura->update([
                        'subtotal'   => $this->subtotal,
                        'impuestos' => $this->impuestos,
                        'total'     => $this->total,
                    ]);

                    // 1.b) Borro los detalles antiguos
                    $factura->detalles()->delete();
                } else {
                    // 1.c) Creo nueva factura pendiente con valor temporal
                    $factura = Factura::create([
                        'cliente_id'     => $data['cliente_id'],
                        'empleado_id'    => auth()->user()->empleado->id,
                        'empresa_id'     => Cliente::find($data['cliente_id'])->empresa_id,
                        'fecha_factura'  => now(),
                        'estado'         => 'Pendiente',
                        'subtotal'       => $this->subtotal,
                        'impuestos'      => $this->impuestos,
                        'total'          => $this->total,
                        'numero_factura' => 'TEMP', // nunca null
                        'cai_id'         => null,
                        'apertura_id'    => session('apertura_id'),
                    ]);

                    // 1.d) Asigno el número definitivo: el ID
                    $factura->update([
                        'numero_factura' => (string) $factura->id,
                    ]);

                    // 1.e) Guardo en sesión para futuras ediciones
                    session(['factura_pendiente_id' => $factura->id]);
                }

                // 2) Creo detalles
                foreach ($this->lineasVenta as $linea) {
                    $inventario = InventarioProductos::find($linea['inventario_id']);
                    $costo      = $inventario?->precio_costo ?? 0;
                    $utilidad   = round($linea['precio_unitario'] - $costo, 2);

                    DetalleFactura::create([
                        'factura_id'         => $factura->id,
                        'producto_id'        => $linea['inventario_id'],
                        'cantidad'           => $linea['cantidad'],
                        'precio_unitario'    => $linea['precio_unitario'],
                        'descuento_aplicado' => $linea['descuento_aplicado'] ?? 0,
                        'sub_total'          => $linea['cantidad'] * $linea['precio_unitario'],
                        'isv_aplicado'       => $linea['isv_producto'] ?? 0,
                        'costo_unitario'     => $costo,
                        'utilidad_unitaria'  => $utilidad,
                    ]);
                }
            });

            Notification::make()
                ->success()
                ->title('Orden guardada como pendiente')
                ->send();

            redirect(FacturaResource::getUrl('view', ['record' => session('factura_pendiente_id')]));
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error al guardar pendiente')
                ->body($e->getMessage())
                ->send();
        }
    }





    protected function getHeaderActions(): array
    {
        return [
            Action::make('facturaPendiente')
                ->label('Factura Pendiente')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->requiresConfirmation()
                ->action('guardarPendiente'),
            Action::make('cerrarCaja')
                ->label('Cerrar Caja / Realizar Arqueo')
                ->color('danger') 
                ->url(CierreCaja::getUrl()), 
        ];
    }
}
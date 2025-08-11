<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenComprasInsumosResource\Pages;
use App\Filament\Resources\OrdenComprasInsumosResource\RelationManagers\DetallesRelationManager;
use App\Models\OrdenComprasInsumos;
use App\Models\TipoOrdenCompras;
use App\Models\Proveedores;
use App\Models\Empresa;
use App\Models\Productos;
use App\Models\CategoriaProducto;
use App\Models\SubcategoriaProducto;
use App\Models\UnidadDeMedidas;
use App\Models\Paises;
use App\Models\Departamento;
use App\Models\Municipio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Filament\Pages\RecibirOrdenCompraInsumos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class OrdenComprasInsumosResource extends Resource
{
    protected static ?string $model = OrdenComprasInsumos::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Insumos y Materia Prima';
    protected static ?string $navigationLabel = 'Órdenes de Compra Insumos';
    protected static ?string $pluralModelLabel = 'Órdenes de Compra Insumos';
    protected static ?string $modelLabel = 'Orden de Compra Insumos';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Orden')
                    ->icon('heroicon-o-rectangle-stack')
                    ->description('Ingrese los detalles de la orden y añada productos. Los detalles se gestionarán en una tabla al editar.')
                    ->schema([
                        Forms\Components\Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->options(\App\Models\Proveedores::pluck('nombre_proveedor', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(100)
                            ->placeholder('Seleccione un proveedor o presione "+"')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('createProveedor')
                                    ->label('Agregar')
                                    ->icon('heroicon-o-plus')
                                    ->tooltip('Agregar un nuevo proveedor')
                                    ->modalHeading('Crear Nuevo Proveedor')
                                    ->form([
                                        Forms\Components\TextInput::make('nombre_proveedor')
                                            ->label('Nombre del Proveedor')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('telefono')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->required()
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('rtn')
                                            ->label('RTN')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('persona_contacto')
                                            ->label('Persona de Contacto')
                                            ->maxLength(255),
                                        Forms\Components\Select::make('empresa_id')
                                            ->label('Empresa')
                                            ->relationship('empresa', 'nombre')
                                            ->searchable()
                                            ->required()
                                            ->default(fn () => Filament::auth()->user()?->empresa_id)
                                            ->disabled()
                                            ->dehydrated(true),
                                        Forms\Components\Select::make('pais_id')
                                            ->label('País')
                                            ->searchable()
                                            ->options(Paises::pluck('nombre_pais', 'id'))
                                            ->placeholder('Seleccione un país')
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set) => [
                                                $set('departamento_id', null),
                                                $set('municipio_id', null),
                                            ]),
                                        Forms\Components\Select::make('departamento_id')
                                            ->label('Departamento')
                                            ->searchable()
                                            ->placeholder('Seleccione un Departamento')
                                            ->options(fn (callable $get) =>
                                                Departamento::where('pais_id', $get('pais_id'))
                                                    ->pluck('nombre_departamento', 'id')
                                            )
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set) => $set('municipio_id', null))
                                            ->disabled(fn (callable $get) => !$get('pais_id')),
                                        Forms\Components\Select::make('municipio_id')
                                            ->label('Municipio')
                                            ->searchable()
                                            ->placeholder('Seleccione un Municipio')
                                            ->options(fn (callable $get) =>
                                                Municipio::where('departamento_id', $get('departamento_id'))
                                                    ->pluck('nombre_municipio', 'id')
                                            )
                                            ->required()
                                            ->disabled(fn (callable $get) => !$get('departamento_id')),
                                        Forms\Components\Textarea::make('direccion')
                                            ->label('Dirección')
                                            ->columnSpanFull(),
                                    ])
                                    ->action(function (array $data, callable $set) {
                                        $newProveedor = Proveedores::create($data);
                                        Notification::make()
                                            ->title('Proveedor Creado')
                                            ->body("El proveedor {$newProveedor->nombre_proveedor} ha sido creado con éxito.")
                                            ->success()
                                            ->send();
                                        $set('proveedor_id', $newProveedor->id);
                                        $set('empresa_id', $newProveedor->empresa_id);
                                    })
                                    ->slideOver()
                            )
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $proveedor = \App\Models\Proveedores::find($state);
                                $empresaId = $proveedor?->empresa_id ?? (Auth::user()->empresa_id ?? \App\Models\Empresa::first()->id);
                                $set('empresa_id', $empresaId);
                            })
                            ->columnSpan(1)
                            ->helperText('Seleccione el proveedor de la orden o cree uno nuevo.'),
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->options(\App\Models\Empresa::pluck('nombre', 'id'))
                            ->required()
                            ->default(function () {
                                $empresaId = Auth::user()->empresa_id;
                                if (!$empresaId) {
                                    Log::warning('Usuario sin empresa_id asignado', ['user_id' => Auth::user()->id]);
                                    $empresaId = \App\Models\Empresa::first()->id ?? null;
                                }
                                return $empresaId;
                            })
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpan(1)
                            ->helperText('La empresa se asigna automáticamente según el proveedor.'),
                        Forms\Components\DatePicker::make('fecha_realizada')
                            ->label('Fecha')
                            ->required()
                            ->default(now())
                            ->columnSpan(1)
                            ->helperText('Seleccione la fecha de la orden.'),
                        Forms\Components\Repeater::make('detalles')
                            ->label('Detalles de la Orden')
                            ->relationship('detalles')
                            ->schema([
                                Forms\Components\Select::make('tipo_orden_compra_id')
                                    ->label('Tipo de Orden')
                                    ->options(
                                        \App\Models\TipoOrdenCompras::whereIn('nombre', ['Insumos', 'Materia Prima'])
                                            ->pluck('nombre', 'id')
                                    )
                                    ->required()
                                    ->reactive()
                                    ->searchable()
                                    ->columnSpan(1)
                                    ->helperText('Seleccione si es Insumos o Materia Prima.')
                                    ->afterStateUpdated(fn (callable $set) => $set('producto_id', null)),
                                Forms\Components\Select::make('producto_id')
                                    ->label('Producto')
                                    ->relationship('producto', 'nombre', function ($query, $get) {
                                        $tipoOrdenId = $get('tipo_orden_compra_id');
                                        $categorias = collect(); // Inicializar como colección vacía
                                        if ($tipoOrdenId) {
                                            $tipo = \App\Models\TipoOrdenCompras::find($tipoOrdenId);
                                            $categoriaNombre = $tipo?->nombre ?? null;
                                            $searchTerm = $categoriaNombre === 'Insumos' ? 'insumo' : strtolower($categoriaNombre);
                                            $categorias = \App\Models\CategoriaProducto::whereRaw('LOWER(nombre) LIKE ?', ["%$searchTerm%"])
                                                ->pluck('id');
                                            $query->whereIn('categoria_id', $categorias);
                                        }
                                        Log::info('Productos cargados para producto_id', [
                                            'tipo_orden_compra_id' => $tipoOrdenId,
                                            'categorias' => $categorias->toArray(),
                                        ]);
                                        return $query;
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->columnSpan(1)
                                    ->helperText('Seleccione un producto.')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            Log::info('Producto seleccionado en repeater', [
                                                'producto_id' => $state,
                                                'nombre' => Productos::find($state)?->nombre,
                                            ]);
                                            $set('producto_id', $state);
                                        }
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
                                                    ->unique(ignorable: fn ($record) => $record),
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
                                                    ->relationship('unidadDeMedida', 'nombre')
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
                                                                    ->relationship('categoria', 'nombre')
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
                                                            ->slideOver()
                                                    ),
                                                Forms\Components\Select::make('categoria_id')
                                                    ->label('Categoría')
                                                    ->relationship('categoria', 'nombre', function ($query, $get) {
                                                        $tipoOrdenId = $get('../../tipo_orden_compra_id');
                                                        $searchTerm = null; // Inicializar $searchTerm
                                                        if ($tipoOrdenId) {
                                                            $tipo = \App\Models\TipoOrdenCompras::find($tipoOrdenId);
                                                            $categoriaNombre = $tipo?->nombre ?? null;
                                                            $searchTerm = $categoriaNombre === 'Insumos' ? 'insumo' : strtolower($categoriaNombre);
                                                            $query->whereRaw('LOWER(nombre) LIKE ?', ["%$searchTerm%"]);
                                                        }
                                                        Log::info('Categorías cargadas para createProducto', [
                                                            'tipo_orden_compra_id' => $tipoOrdenId,
                                                            'search_term' => $searchTerm ?? 'none',
                                                        ]);
                                                        return $query;
                                                    })
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->reactive()
                                                    ->afterStateUpdated(fn (callable $set) => $set('subcategoria_id', null))
                                                    ->placeholder('Seleccione una categoría o presione "+"')
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
                                                                    ->helperText('El nombre debe ser claro y representativo de la categoría.')
                                                                    ->live(onBlur: true)
                                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                                        $empresaId = auth()->user()->empresa_id ?? null;
                                                                        if (!$empresaId && !(auth()->user()->is_root ?? false)) {
                                                                            Log::warning('Usuario sin empresa_id al intentar crear categoría', ['user_id' => auth()->user()->id]);
                                                                            Notification::make()
                                                                                ->title('Error')
                                                                                ->body('No se puede crear una categoría sin una empresa asignada.')
                                                                                ->danger()
                                                                                ->persistent()
                                                                                ->send();
                                                                            $set('nombre', null);
                                                                            return;
                                                                        }
                                                                        $existing = CategoriaProducto::where('nombre', $state)
                                                                            ->when($empresaId, fn ($query) => $query->where('empresa_id', $empresaId))
                                                                            ->first();
                                                                        if ($existing) {
                                                                            Log::info('Categoría existente encontrada', ['nombre' => $state, 'categoria_id' => $existing->id]);
                                                                            Notification::make()
                                                                                ->title('Categoría ya existente')
                                                                                ->body('La categoría "' . $state . '" ya existe. ¿Desea añadir más subcategorías a esta categoría?')
                                                                                ->actions([
                                                                                    \Filament\Notifications\Actions\Action::make('yes')
                                                                                        ->label('Sí, añadir subcategorías')
                                                                                        ->button()
                                                                                        ->color('success')
                                                                                        ->url(CategoriaProductoResource::getUrl('edit', ['record' => $existing->id]))
                                                                                        ->close(),
                                                                                    \Filament\Notifications\Actions\Action::make('no')
                                                                                        ->label('No, cancelar')
                                                                                        ->color('danger')
                                                                                        ->close(),
                                                                                ])
                                                                                ->persistent()
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
                                                                            ->placeholder('Ejemplo: Camisetas, Laptops')
                                                                            ->helperText('Añada subcategorías específicas para esta categoría.')
                                                                            ->columnSpanFull(),
                                                                    ])
                                                                    ->required()
                                                                    ->minItems(1)
                                                                    ->grid([
                                                                        'default' => 4,
                                                                        'sm' => 2,
                                                                        'xs' => 1,
                                                                    ])
                                                                    ->itemLabel(fn (array $state): ?string => $state['nombre'] ?? 'Nueva subcategoría')
                                                                    ->collapsible()
                                                                    ->addActionLabel('Añadir Subcategoría')
                                                                    ->deleteAction(
                                                                        fn ($action) => $action->requiresConfirmation()->label('Eliminar Subcategoría')
                                                                    )
                                                                    ->extraAttributes(['class' => 'gap-4']),
                                                                Forms\Components\Hidden::make('empresa_id')
                                                                    ->default(function () {
                                                                        $user = Filament::auth()->user();
                                                                        if (!$user->empresa_id) {
                                                                            Log::error('Usuario sin empresa_id al intentar crear categoría', ['user_id' => $user->id]);
                                                                            Notification::make()
                                                                                ->title('Error')
                                                                                ->body('No se puede crear una categoría sin una empresa asignada.')
                                                                                ->danger()
                                                                                ->persistent()
                                                                                ->send();
                                                                            throw new \Exception('El usuario no tiene una empresa asignada.');
                                                                        }
                                                                        return $user->empresa_id;
                                                                    })
                                                                    ->required()
                                                                    ->dehydrated(true),
                                                            ])
                                                            ->action(function (array $data, callable $set, $form, $get) {
                                                                Log::info('Iniciando creación de categoría', ['data' => $data]);
                                                                $empresaId = auth()->user()->empresa_id;
                                                                if (!$empresaId) {
                                                                    Log::error('No se proporcionó empresa_id', ['user_id' => auth()->user()->id]);
                                                                    Notification::make()
                                                                        ->title('Error')
                                                                        ->body('No se puede crear una categoría sin una empresa asignada.')
                                                                        ->danger()
                                                                        ->persistent()
                                                                        ->send();
                                                                    return;
                                                                }
                                                                if (empty($data['subcategorias'])) {
                                                                    Log::warning('No se proporcionaron subcategorías', ['data' => $data]);
                                                                    Notification::make()
                                                                        ->title('Error')
                                                                        ->body('Debe añadir al menos una subcategoría.')
                                                                        ->danger()
                                                                        ->persistent()
                                                                        ->send();
                                                                    return;
                                                                }

                                                                try {
                                                                    DB::beginTransaction();

                                                                    $newCategoria = CategoriaProducto::create([
                                                                        'nombre' => $data['nombre'],
                                                                        'empresa_id' => $empresaId,
                                                                    ]);
                                                                    Log::info('Categoría creada', [
                                                                        'categoria_id' => $newCategoria->id,
                                                                        'nombre' => $newCategoria->nombre,
                                                                        'empresa_id' => $empresaId,
                                                                    ]);

                                                                    $subcategorias = [];
                                                                    foreach ($data['subcategorias'] as $subcategoria) {
                                                                        $newSubcategoria = SubcategoriaProducto::create([
                                                                            'nombre' => $subcategoria['nombre'],
                                                                            'categoria_id' => $newCategoria->id,
                                                                            'empresa_id' => $empresaId,
                                                                        ]);
                                                                        $subcategorias[] = $newSubcategoria;
                                                                        Log::info('Subcategoría creada', [
                                                                            'subcategoria_id' => $newSubcategoria->id,
                                                                            'nombre' => $newSubcategoria->nombre,
                                                                            'categoria_id' => $newCategoria->id,
                                                                        ]);
                                                                    }

                                                                    Log::info('Categoría y subcategorías creadas', [
                                                                        'categoria_id' => $newCategoria->id,
                                                                        'nombre' => $newCategoria->nombre,
                                                                        'subcategorias' => array_map(fn($sub) => ['id' => $sub->id, 'nombre' => $sub->nombre], $subcategorias),
                                                                    ]);

                                                                    $set('categoria_id', $newCategoria->id);

                                                                    if (count($subcategorias) === 1) {
                                                                        $set('subcategoria_id', $subcategorias[0]->id);
                                                                        Log::info('Subcategoría única seleccionada', [
                                                                            'subcategoria_id' => $subcategorias[0]->id,
                                                                            'nombre' => $subcategorias[0]->nombre,
                                                                        ]);
                                                                    } else {
                                                                        $set('subcategoria_id', null);
                                                                        $subcategoriaComponent = $form->getComponent('subcategoria_id');
                                                                        $subcategoriaOptions = SubcategoriaProducto::where('categoria_id', $newCategoria->id)
                                                                            ->pluck('nombre', 'id')
                                                                            ->toArray();
                                                                        $subcategoriaComponent->options($subcategoriaOptions);
                                                                        Log::info('Múltiples subcategorías creadas, opciones actualizadas', [
                                                                            'subcategoria_options' => $subcategoriaOptions,
                                                                        ]);
                                                                    }

                                                                    DB::commit();

                                                                    Notification::make()
                                                                        ->title('Categoría Creada')
                                                                        ->body("La categoría {$newCategoria->nombre} ha sido creada con éxito.")
                                                                        ->success()
                                                                        ->persistent()
                                                                        ->send();
                                                                    Log::info('Notificación de categoría creada enviada', ['categoria_id' => $newCategoria->id]);
                                                                } catch (\Exception $e) {
                                                                    DB::rollBack();
                                                                    Log::error('Error al crear categoría o subcategorías', [
                                                                        'error' => $e->getMessage(),
                                                                        'trace' => $e->getTraceAsString(),
                                                                    ]);
                                                                    Notification::make()
                                                                        ->title('Error')
                                                                        ->body('No se pudo crear la categoría o las subcategorías: ' . $e->getMessage())
                                                                        ->danger()
                                                                        ->persistent()
                                                                        ->send();
                                                                }
                                                            })
                                                            ->slideOver()
                                                    ),
                                                Forms\Components\Select::make('subcategoria_id')
                                                    ->label('Subcategoría')
                                                    ->relationship('subcategoria', 'nombre', fn (Builder $query, $get) => $query->where('categoria_id', $get('categoria_id')))
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->disabled(fn ($get) => !$get('categoria_id'))
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            Log::info('Subcategoría seleccionada', [
                                                                'subcategoria_id' => $state,
                                                                'nombre' => SubcategoriaProducto::find($state)?->nombre,
                                                            ]);
                                                            $set('subcategoria_id', $state);
                                                        }
                                                    }),
                                                Forms\Components\Hidden::make('empresa_id')
                                                    ->default(function () {
                                                        $user = Filament::auth()->user();
                                                        if (!$user->empresa_id) {
                                                            Log::error('Usuario sin empresa_id al intentar crear producto', ['user_id' => $user->id]);
                                                            Notification::make()
                                                                ->title('Error')
                                                                ->body('No tienes una empresa asignada. Contacta al administrador.')
                                                                ->danger()
                                                                ->persistent()
                                                                ->send();
                                                            throw new \Exception('El usuario no tiene una empresa asignada.');
                                                        }
                                                        return $user->empresa_id;
                                                    })
                                                    ->required()
                                                    ->dehydrated(true),
                                                Forms\Components\TextInput::make('sku')
                                                    ->label('SKU')
                                                    ->maxLength(100)
                                                    ->default(fn () => strtoupper(\Illuminate\Support\Str::random(3) . '-' . rand(10000, 99999)))
                                                    ->unique(ignorable: fn ($record) => $record),
                                                Forms\Components\TextInput::make('codigo')
                                                    ->label('Código de barras')
                                                    ->maxLength(100)
                                                    ->default(fn () => \Illuminate\Support\Str::random(8))
                                                    ->unique(ignorable: fn ($record) => $record),
                                                Forms\Components\TextInput::make('isv')
                                                    ->label('ISV')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->maxValue(0.15)
                                                    ->default(0),
                                            ])
                                            ->action(function (array $data, callable $set, $form, $get) {
                                                Log::info('Iniciando creación de producto', ['data' => $data]);
                                                try {
                                                    $newProducto = Productos::create($data);
                                                    Log::info('Producto creado', [
                                                        'producto_id' => $newProducto->id,
                                                        'nombre' => $newProducto->nombre,
                                                        'descripcion_corta' => $newProducto->descripcion_corta,
                                                        'descripcion' => $newProducto->descripcion,
                                                        'color' => $newProducto->color,
                                                        'categoria_id' => $newProducto->categoria_id,
                                                        'subcategoria_id' => $newProducto->subcategoria_id,
                                                        'unidad_de_medida_id' => $newProducto->unidad_de_medida_id,
                                                        'sku' => $newProducto->sku,
                                                        'codigo' => $newProducto->codigo,
                                                        'isv' => $newProducto->isv,
                                                        'empresa_id' => $newProducto->empresa_id,
                                                    ]);
                                                    // Establecer el producto_id en el repeater
                                                    $set('../../producto_id', $newProducto->id);
                                                    Log::info('Producto_id establecido en el repeater', [
                                                        'path' => '../../producto_id',
                                                        'producto_id' => $newProducto->id,
                                                        'nombre' => $newProducto->nombre,
                                                    ]);
                                                    Notification::make()
                                                        ->title('Producto Creado')
                                                        ->body("El producto {$newProducto->nombre} ha sido creado con éxito.")
                                                        ->success()
                                                        ->persistent()
                                                        ->send();
                                                    Log::info('Notificación de producto creado enviada', ['producto_id' => $newProducto->id]);
                                                } catch (\Exception $e) {
                                                    Log::error('Error al crear producto', [
                                                        'error' => $e->getMessage(),
                                                        'trace' => $e->getTraceAsString(),
                                                    ]);
                                                    Notification::make()
                                                        ->title('Error')
                                                        ->body('No se pudo crear el producto: ' . $e->getMessage())
                                                        ->danger()
                                                        ->persistent()
                                                        ->send();
                                                }
                                            })
                                            ->slideOver()
                                    ),
                                Forms\Components\TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $precioUnitario = $get('precio_unitario') ?? 0;
                                        $set('subtotal', $state * $precioUnitario);
                                    })
                                    ->columnSpan(1)
                                    ->helperText('Ingrese la cantidad de productos.'),
                                Forms\Components\TextInput::make('precio_unitario')
                                    ->label('Precio Unitario (HNL)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $cantidad = $get('cantidad') ?? 0;
                                        $set('subtotal', $cantidad * $state);
                                    })
                                    ->columnSpan(1)
                                    ->helperText('Ingrese el precio por unidad en Lempiras.'),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal (HNL)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(1)
                                    ->helperText('Calculado automáticamente.'),
                                Forms\Components\Section::make('Análisis de Calidad')
                                    ->schema([
                                        Forms\Components\TextInput::make('porcentaje_grasa')
                                            ->label('Porcentaje de Grasa (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->required()
                                            ->helperText('Ejemplo: 3.50'),
                                        Forms\Components\TextInput::make('porcentaje_proteina')
                                            ->label('Porcentaje de Proteína (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->required()
                                            ->helperText('Ejemplo: 2.80'),
                                        Forms\Components\TextInput::make('porcentaje_humedad')
                                            ->label('Porcentaje de Humedad (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->required()
                                            ->helperText('Ejemplo: 87.00'),
                                        Forms\Components\Toggle::make('anomalias')
                                            ->label('¿Tiene Anomalías?')
                                            ->default(false)
                                            ->reactive()
                                            ->live()
                                            ->helperText('Marque si se detectaron anomalías.'),
                                        Forms\Components\Textarea::make('detalles_anomalias')
                                            ->label('Detalles de Anomalías')
                                            ->maxLength(255)
                                            ->live()
                                            ->visible(fn ($get) => $get('anomalias'))
                                            ->helperText('Describa las anomalías detectadas, si las hay.'),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->visible(fn ($get) => \App\Filament\Resources\OrdenComprasInsumosResource\RelationManagers\DetallesRelationManager::esMateriaPrima($get('tipo_orden_compra_id')))
                                    ->columnSpanFull(),
                            ])
                            ->grid([
                                'default' => 4,
                                'sm' => 2,
                                'xs' => 1,
                            ])
                            ->itemLabel(fn (array $state): ?string => \App\Models\Productos::find($state['producto_id'])?->nombre ?? 'Nuevo detalle')
                            ->collapsible()
                            ->addActionLabel('Añadir Detalle')
                            ->deleteAction(
                                fn ($action) => $action->requiresConfirmation()->label('Eliminar Detalle')
                            )
                            ->hidden(fn ($context) => $context === 'edit' || $context === 'view')
                            ->extraAttributes(['class' => 'gap-4'])
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('proveedor.nombre_proveedor')
                    ->label('Proveedor')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('detalles_count')
                    ->label('Productos')
                    ->getStateUsing(fn ($record) => $record->detalles()->count())
                    ->formatStateUsing(fn ($state) => $state ?? '0')
                    ->sortable()
                    ->description(fn ($record) => $record->detalles()->count() . ' productos'),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'Pendiente' => 'Orden Abierta',
                            'Recibida' => 'Orden en Inventario',
                            default => $state
                        };
                    })
                    ->tooltip(function ($state) {
                        return match ($state) {
                            'Pendiente' => 'La orden ha sido registrada pero aún no se ha recibido en inventario.',
                            'Recibida' => 'La orden de compra ha sido recibida y registrada en el inventario.',
                            default => 'Estado no definido.'
                        };
                    }),
                Tables\Columns\IconColumn::make('anomalias')
                    ->label('Anomalías')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->getStateUsing(fn ($record) => $record->detalles && $record->detalles->contains('anomalias', true))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Pendiente' => 'Orden Abierta',
                        'Recibida' => 'Orden en Inventario',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->where('estado', $data['value']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->disabled(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida'),
                    Tables\Actions\Action::make('recibirEnInventario')
                        ->label('Recibir en Inventario')
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->color('success')
                        ->hidden(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida')
                        ->url(fn (OrdenComprasInsumos $record): string => \App\Filament\Pages\RecibirOrdenCompraInsumos::getUrl(['orden_id' => $record->id])),
                    Tables\Actions\Action::make('generatePdf')
                        ->label('Generar PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->hidden(fn (OrdenComprasInsumos $record): bool => $record->estado !== 'Recibida')
                        ->action(function (OrdenComprasInsumos $record) {
                            $pdf = Pdf::loadView('pdf.orden-compra-insumos', [
                                'orden' => $record->load(['empresa', 'proveedor', 'detalles.producto', 'detalles.tipoOrdenCompra']),
                                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                            ]);
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, "orden-compra-insumos-{$record->id}.pdf");
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->disabled(fn (OrdenComprasInsumos $record): bool => $record->estado === 'Recibida'),
                ])
                    ->label('Acciones')
                    ->button()
                    ->outlined()
                    ->dropdown(true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->requiresConfirmation()
                        ->disabled(fn ($records) => $records && $records->contains(fn ($record) => $record->estado === 'Recibida')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DetallesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenComprasInsumos::route('/'),
            'create' => Pages\CreateOrdenComprasInsumos::route('/create'),
            'edit' => Pages\EditOrdenComprasInsumos::route('/{record}/edit'),
            'view' => Pages\ViewOrdenComprasInsumos::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['detalles']);
    }
}
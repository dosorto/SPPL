<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductosResource\Pages;
use App\Models\Productos;
use App\Models\CategoriaProducto;
use App\Models\SubcategoriaProducto;
use App\Models\UnidadDeMedidas;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductosResource extends Resource
{
    protected static ?string $model = Productos::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $navigationGroup = 'Inventario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos principales')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del producto')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignorable: fn ($record) => $record),
                        Forms\Components\Select::make('unidad_de_medida_id')
                            ->label('Unidad de medida')
                            ->relationship('unidadDeMedida', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Seleccione una unidad o presione "+"')
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
                            ->relationship('categoria', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->default(request()->query('categoria_id'))
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
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $empresaId = auth()->user()->empresa_id ?? null;
                                                if (!$empresaId && !(auth()->user()->is_root ?? false)) {
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
                                    ->action(function (array $data, callable $set, callable $get, $component) {
                                        $empresaId = auth()->user()->empresa_id;
                                        if (empty($data['subcategorias'])) {
                                            Notification::make()
                                                ->title('Error')
                                                ->body('Debe añadir al menos una subcategoría.')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        try {
                                            DB::beginTransaction();

                                            $newCategoria = CategoriaProducto::create([
                                                'nombre' => $data['nombre'],
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
                                                $subcategoriaOptions = SubcategoriaProducto::where('categoria_id', $newCategoria->id)
                                                    ->pluck('nombre', 'id')
                                                    ->toArray();
                                                $subcategoriaComponent = $form->getComponent('subcategoria_id');
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
                                                ->send();
                                        } catch (\Exception $e) {
                                            DB::rollBack();
                                            Log::error('Error al crear categoría o subcategorías', [
                                                'error' => $e->getMessage(),
                                                'trace' => $e->getTraceAsString(),
                                            ]);
                                            Notification::make()
                                                ->title('Error')
                                                ->body('No se pudo crear la categoría o las subcategorías. Por favor, intenta de nuevo.')
                                                ->danger()
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
                            ->default(function () {
                                $categoriaId = request()->query('categoria_id');
                                if ($categoriaId) {
                                    $subcategoria = SubcategoriaProducto::where('categoria_id', $categoriaId)->first();
                                    return $subcategoria ? $subcategoria->id : null;
                                }
                                return null;
                            })
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
                                    Notification::make()
                                        ->title('Error')
                                        ->body('No tienes una empresa asignada. Contacta al administrador.')
                                        ->danger()
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
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Section::make('Detalles adicionales')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('descripcion_corta')
                            ->label('Descripción corta')
                            ->rows(2)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción larga')
                            ->rows(4)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('color')
                            ->label('Color')
                            ->maxLength(50),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Section::make('Imágenes')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('fotos')
                            ->label('Fotos del producto')
                            ->multiple()
                            ->directory('productos')
                            ->image()
                            ->maxSize(2048)
                            ->reorderable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                            ->helperText('Puedes subir varias imágenes')
                            ->saveRelationshipsUsing(function ($component, $state, $record) {
                                if ($record && $state) {
                                    $record->fotosRelacion()->delete();
                                    foreach ($state as $file) {
                                        $record->fotosRelacion()->create(['url' => $file]);
                                    }
                                }
                            })
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->fotosRelacion) {
                                    $component->state($record->fotosRelacion->pluck('url')->toArray());
                                }
                            }),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unidadDeMedida.nombre')
                    ->label('Unidad de Medida')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subcategoria.nombre')
                    ->label('Subcategoría')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => Filament::auth()->user()->hasRole('root')),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código de Barras')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ViewColumn::make('codigo')
                    ->label('Código de Barras')
                    ->view('filament.tables.columns.codigo-barra'),
                Tables\Columns\TextColumn::make('isv')
                    ->label('ISV')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
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
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                ->label('Acciones')
                ->button()
                ->outlined(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Agrega aquí el RelationManager para fotos si es necesario
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProductos::route('/create'),
            'edit' => Pages\EditProductos::route('/{record}/edit'),
            'view' => Pages\ViewProductos::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $query = parent::getEloquentQuery()->with(['unidadDeMedida', 'empresa', 'categoria', 'subcategoria']);
        if (!$user->hasRole('root')) {
            $query->where('empresa_id', $user->empresa_id);
        }
        return $query;
    }
}
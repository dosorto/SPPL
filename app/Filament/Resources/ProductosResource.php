<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductosResource\Pages;
use App\Models\Productos;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                            ->maxLength(100),
                        Forms\Components\Select::make('unidad_de_medida_id')
                            ->label('Unidad de medida')
                            ->relationship('unidadDeMedida', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('categoria_id')
                            ->label('Categoría')
                            ->relationship('categoria', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('subcategoria_id', null); // Resetear subcategoría al cambiar categoría
                            }),
                        Forms\Components\Select::make('subcategoria_id')
                            ->label('Subcategoría')
                            ->relationship('subcategoria', 'nombre', function ($query, $get) {
                                $categoriaId = $get('categoria_id');
                                if ($categoriaId) {
                                    $query->where('categoria_id', $categoriaId);
                                }
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($get) => !$get('categoria_id')),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('codigo')
                            ->label('Código de barras')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('isv')
                            ->label('ISV')
                            ->numeric(),
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nombre')
                            ->default(fn () => Filament::auth()->user()?->empresa_id)
                            ->hidden()
                            ->dehydrated(true),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Section::make('Detalles adicionales')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('descripcion_corta')
                            ->label('Descripción corta')
                            ->rows(2),
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción larga')
                            ->rows(4),
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
                            ->default(fn ($record) => $record?->fotosRelacion?->pluck('url')->toArray() ?? [])
                            ->afterStateHydrated(function ($component, $state) {
                                $record = $component->getRecord();
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
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                $search = $table->getLivewire()->tableSearch;
                if ($search) {
                    $query->where(function (Builder $subQuery) use ($search) {
                        $subQuery->where('nombre', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('codigo', 'like', "%{$search}%");
                    });
                }
            })
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Editar'),
                    Tables\Actions\DeleteAction::make()->label('Eliminar'),
                    Tables\Actions\ViewAction::make()->label('Ver'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
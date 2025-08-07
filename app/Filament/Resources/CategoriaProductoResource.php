<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaProductoResource\Pages;
use App\Filament\Resources\CategoriaProductoResource\RelationManagers\SubcategoriasRelationManager;
use App\Filament\Resources\ProductosResource;
use App\Models\CategoriaProducto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriaProductoResource extends Resource
{
    protected static ?string $model = CategoriaProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Categorías de Productos';
    protected static ?string $pluralModelLabel = 'Categorías de Productos';
    protected static ?string $modelLabel = 'Categoría de Producto';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Categoría')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre de la Categoría')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignorable: fn ($record) => $record),
                        Forms\Components\Repeater::make('subcategorias')
                            ->label('Subcategorías')
                            ->relationship('subcategorias')
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre de la Subcategoría')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignorable: fn ($record) => $record),
                            ])
                            ->columns(1)
                            ->itemLabel(fn (array $state): ?string => $state['nombre'] ?? null)
                            ->collapsible(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subcategorias')
                    ->label('Subcategorías')
                    ->formatStateUsing(fn ($record) => $record->subcategorias->pluck('nombre')->join(', '))
                    ->sortable()
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
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
                    Tables\Actions\Action::make('create_product')
                        ->label('Registrar Nuevo Producto')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->url(fn ($record): string => ProductosResource::getUrl('create', [
                            'categoria_id' => $record->id,
                            'subcategoria_id' => $record->subcategorias->first()->id ?? null,
                        ])),
                ])
                ->label('Acciones')
                ->button()
                ->outlined()
                ->dropdown(true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SubcategoriasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaProductos::route('/'),
            'create' => Pages\CreateCategoriaProducto::route('/create'),
            'edit' => Pages\EditCategoriaProducto::route('/{record}/edit'),
            'view' => Pages\ViewCategoriaProductos::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['subcategorias']);
    }
}
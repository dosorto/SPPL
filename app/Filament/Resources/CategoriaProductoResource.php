<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaProductoResource\Pages;
use App\Models\CategoriaProducto;
use Filament\Facades\Filament;
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
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nombre')
                            ->default(fn () => Filament::auth()->user()?->empresa_id)
                            ->required()
                            ->hidden(fn () => !Filament::auth()->user()->hasRole('root'))
                            ->dehydrated(true)
                            ->searchable()
                            ->preload(),
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
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->visible(fn () => Filament::auth()->user()->hasRole('root')),
                Tables\Columns\TextColumn::make('subcategorias')
                    ->label('Subcategorías')
                    ->formatStateUsing(fn ($record) => $record->subcategorias->pluck('nombre')->join(', '))
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('productos')
                    ->label('Productos')
                    ->formatStateUsing(fn ($record) => $record->productos->pluck('nombre')->join(', '))
                    ->sortable()
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'nombre')
                    ->visible(fn () => Filament::auth()->user()->hasRole('root')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Editar'),
                    Tables\Actions\DeleteAction::make()->label('Eliminar'),
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
            'index' => Pages\ListCategoriaProductos::route('/'),
            'create' => Pages\CreateCategoriaProducto::route('/create'),
            'edit' => Pages\EditCategoriaProducto::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $query = parent::getEloquentQuery()->with(['empresa', 'subcategorias', 'productos']);
        if (!$user->hasRole('root')) {
            $query->where('empresa_id', $user->empresa_id);
        }
        return $query;
    }
}
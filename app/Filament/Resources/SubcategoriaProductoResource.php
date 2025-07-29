<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubcategoriaProductoResource\Pages;
use App\Models\SubcategoriaProducto;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubcategoriaProductoResource extends Resource
{
    protected static ?string $model = SubcategoriaProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationLabel = 'Subcategorías de Productos';
    protected static ?string $pluralModelLabel = 'Subcategorías de Productos';
    protected static ?string $modelLabel = 'Subcategoría de Producto';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Subcategoría')
                    ->icon('heroicon-o-squares-plus')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre de la Subcategoría')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignorable: fn ($record) => $record),
                        Forms\Components\Select::make('categoria_id')
                            ->label('Categoría')
                            ->relationship('categoria', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload(),
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
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->visible(fn () => Filament::auth()->user()->hasRole('root')),
                Tables\Columns\TextColumn::make('productos')
                    ->label('Productos')
                    ->formatStateUsing(fn ($record) => $record->productos->pluck('nombre')->join(', '))
                    ->sortable()
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
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
            'index' => Pages\ListSubcategoriaProductos::route('/'),
            'create' => Pages\CreateSubcategoriaProducto::route('/create'),
            'edit' => Pages\EditSubcategoriaProducto::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $query = parent::getEloquentQuery()->with(['categoria', 'empresa', 'productos']);
        if (!$user->hasRole('root')) {
            $query->where('empresa_id', $user->empresa_id);
        }
        return $query;
    }
}
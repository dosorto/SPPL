<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaProductoResource\Pages;
use App\Filament\Resources\CategoriaProductoResource\RelationManagers\SubcategoriasRelationManager;
use App\Filament\Resources\ProductosResource;
use App\Models\CategoriaProducto;
use App\Models\SubcategoriaProducto;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
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
                Section::make('Datos de la Categoría')
                    ->icon('heroicon-o-squares-2x2')
                    ->description(fn ($context) => $context === 'create'
                        ? 'Ingrese el nombre de la categoría y añada subcategorías. Las subcategorías se gestionarán en una pestaña al editar.'
                        : 'Ingrese el nombre de la categoría. Las subcategorías se gestionan en la pestaña inferior.')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre de la Categoría')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ejemplo: Ropa, Electrónica')
                            ->helperText('El nombre debe ser claro y representativo de la categoría.')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $context, callable $set) {
                                if ($context === 'edit') {
                                    return;
                                }
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
                                            NotificationAction::make('yes')
                                                ->label('Sí, añadir subcategorías')
                                                ->button()
                                                ->color('success')
                                                ->url(self::getUrl('edit', ['record' => $existing->id]))
                                                ->close(),
                                            NotificationAction::make('no')
                                                ->label('No, cancelar')
                                                ->color('danger')
                                                ->close(),
                                        ])
                                        ->persistent()
                                        ->send();
                                    $set('nombre', null);
                                }
                            }),
                        // Repeater solo en la vista de creación
                        Repeater::make('subcategorias')
                            ->label('Subcategorías')
                            ->relationship('subcategorias')
                            ->schema([
                                TextInput::make('nombre')
                                    ->label('Nombre de la Subcategoría')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ejemplo: Camisetas, Laptops')
                                    ->helperText('Añada subcategorías específicas para esta categoría.')
                                    ->columnSpanFull(),
                            ])
                            ->grid([
                                'default' => 4, // 4 columnas por defecto
                                'sm' => 2, // 2 columnas en pantallas pequeñas
                                'xs' => 1, // 1 columna en pantallas muy pequeñas
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['nombre'] ?? 'Nueva subcategoría')
                            ->collapsible()
                            ->addActionLabel('Añadir Subcategoría')
                            ->deleteAction(
                                fn ($action) => $action->requiresConfirmation()->label('Eliminar Subcategoría')
                            )
                            ->hidden(fn ($context) => $context === 'edit') // Oculta el Repeater en edición
                            ->extraAttributes(['class' => 'gap-4']),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre de la Categoría')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->subcategorias->count() . ' subcategorías')
                    ->wrap(),
                Tables\Columns\TextColumn::make('subcategorias')
                    ->label('Subcategorías')
                    ->formatStateUsing(fn ($record) => $record->subcategorias->pluck('nombre')->join(', ') ?: 'Sin subcategorías')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('subcategorias')
                    ->label('Con Subcategorías')
                    ->options([
                        'with' => 'Con subcategorías',
                        'without' => 'Sin subcategorías',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'with') {
                            $query->has('subcategorias');
                        } elseif ($data['value'] === 'without') {
                            $query->doesntHave('subcategorias');
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
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('create_product')
                        ->label('Registrar Producto')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->url(fn ($record): string => ProductosResource::getUrl('create', [
                            'categoria_id' => $record->id,
                            'subcategoria_id' => $record->subcategorias->first()->id ?? null,
                        ]))
                        ->tooltip('Crear un nuevo producto en esta categoría'),
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
                        ->requiresConfirmation(),
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
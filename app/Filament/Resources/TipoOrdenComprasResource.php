<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoOrdenComprasResource\Pages;
use App\Models\TipoOrdenCompras;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;

class TipoOrdenComprasResource extends Resource
{
    protected static ?string $model = TipoOrdenCompras::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Tipos de Orden de Compra';
    protected static ?string $pluralModelLabel = 'Tipos de Orden de Compra';
    protected static ?string $modelLabel = 'Tipo de Orden de Compra';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles del Tipo de Orden')
                    ->icon('heroicon-o-receipt-refund')
                    ->description('Configure el tipo de orden de compra.')
                    ->schema([
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nombre')
                            ->required()
                            ->helperText('Seleccione la empresa asociada.'),
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Tipo de Orden')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej. Insumos, Materia Prima, Urgente...')
                            ->unique(ignoreRecord: true)
                            ->helperText('Ejemplo: Insumos, Materia Prima'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Tipo de Orden')
                    ->badge()
                    ->color('primary')
                    ->tooltip('Nombre del tipo de orden.'),
                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->tooltip('Nombre de la empresa asociada.'),
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn ($record) => now()->diffInDays($record->created_at) <= 7 ? 'success' : null)
                    ->tooltip('Fecha de creación del registro.'),
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn ($record) => now()->diffInDays($record->updated_at) <= 7 ? 'warning' : null)
                    ->tooltip('Fecha de última actualización.'),
                TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('danger')
                    ->tooltip('Fecha de eliminación, si aplica.'),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Ver Eliminados'),
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
                        ->requiresConfirmation()
                        ->modalHeading('Editar Tipo de Orden')
                        ->modalDescription('Confirme los cambios en el tipo de orden.'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Tipo de Orden')
                        ->modalDescription('¿Está seguro de eliminar este tipo de orden?'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Restaurar')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->requiresConfirmation(),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Eliminar Definitivamente')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation(),
                ])
                    ->label('Acciones')
                    ->button()
                    ->outlined()
                    ->dropdown(true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar')
                        ->requiresConfirmation(),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restaurar')
                        ->requiresConfirmation(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Eliminar Definitivamente')
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('nombre', 'asc')
            ->paginated([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoOrdenCompras::route('/'),
            'create' => Pages\CreateTipoOrdenCompras::route('/create'),
            'edit' => Pages\EditTipoOrdenCompras::route('/{record}/edit'),
        ];
    }

   
}
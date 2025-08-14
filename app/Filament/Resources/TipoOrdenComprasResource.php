<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoOrdenComprasResource\Pages;
use App\Models\TipoOrdenCompras;
use App\Models\Empresa; // Assuming you have an Empresa model
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Tipo de Orden')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej. Maquinaria Lácteos, Insumos Lácteos...')
                            ->unique(
                                table: TipoOrdenCompras::class,
                                column: 'nombre',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule) {
                                    return $rule->where('empresa_id', Auth::user()->empresa_id);
                                }
                            )
                            ->validationMessages([
                                'unique' => 'The nombre del Tipo de Orden has already been taken.',
                            ])
                            ->helperText('Ejemplo: Maquinaria Lácteos, Materia Prima Lácteos'),
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->options(function () {
                                // Assuming Empresa model exists and has a 'nombre' field
                                return Empresa::where('id', Auth::user()->empresa_id)
                                    ->pluck('nombre', 'id')
                                    ->toArray();
                            })
                            ->default(Auth::user()->empresa_id)
                            ->disabled()
                            ->dehydrated(false) // Prevents empresa_id from being included in the form submission
                            ->required(false) // Not required in the form
                            ->helperText('La empresa asociada al usuario actual.'),
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
                TextColumn::make('empresa.nombre') // Assuming Empresa model has a 'nombre' field
                    ->label('Empresa')
                    ->sortable()
                    ->tooltip('Empresa asociada al tipo de orden.'),
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
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                if ($user->hasRole('root')) {
                    return $query;
                }

                return $query->where('empresa_id', $user->empresa_id);
            })

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
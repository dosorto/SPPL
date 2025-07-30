<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaiResource\Pages;
use App\Filament\Resources\CaiResource\RelationManagers;
use App\Models\Cai;
use App\Models\Empresa; // Asegúrate de importar tu modelo Empresa
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;

class CaiResource extends Resource
{
    protected static ?string $model = Cai::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text'; // Icono más descriptivo para CAI
    protected static ?string $navigationGroup = 'Ventas'; // O el grupo de navegación que prefieras

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('cai')
                            ->label('Código CAI')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->maxLength(255),

                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->searchable() // activa buscador
                            ->options(function () {
                                if (auth()->user()->hasRole('root')) {
                                    return \App\Models\Empresa::pluck('nombre', 'id');
                                } else {
                                    $empresa = auth()->user()->empresa;
                                    return $empresa ? [$empresa->id => $empresa->nombre] : [];
                                }
                            })
                            ->default(function () {
                                return auth()->user()->empresa_id;
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                return \App\Models\Empresa::find($value)?->nombre;
                            })
                            ->disabled(!auth()->user()->hasRole('root'))
                            ->required(),

                        TextInput::make('establecimiento')
                            ->label('Establecimiento')
                            ->maxLength(3)
                            ->default('001')
                            ->required()
                            ->disabledOn('edit'),

                        TextInput::make('punto_emision')
                            ->label('Punto de Emisión')
                            ->maxLength(3)
                            ->default('001')
                            ->required()
                            ->disabledOn('edit'),

                        TextInput::make('tipo_documento')
                            ->label('Tipo Documento')
                            ->maxLength(2)
                            ->default('01')
                            ->required()
                            ->helperText('Ej: 01 para factura, 03 para nota de crédito')
                            ->disabledOn('edit'),

                        TextInput::make('rango_inicial')
                            ->label('Rango Inicial')
                            ->numeric()
                            ->required()
                            ->default(1),

                        TextInput::make('rango_final')
                            ->label('Rango Final')
                            ->numeric()
                            ->required()
                            ->default(100),


                        TextInput::make('numero_actual')
                            ->label('Número Actual')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->disabled()
                            ->helperText('Actualizado automáticamente por el sistema.'),


                        DatePicker::make('fecha_limite_emision')
                            ->label('Fecha Límite de Emisión')
                            ->required()
                            ->minDate(now()),

                        Toggle::make('activo')
                            ->label('Activo')
                            ->default(true), // Ocupa ambas columnas para mejor visualización
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cai')
                    ->label('Código CAI')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasRole('root')), // <-- CAMBIO AQUÍ: Visible solo para rol 'root'

                TextColumn::make('rango_inicial')
                    ->label('Rango Inicial')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('rango_final')
                    ->label('Rango Final')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('numero_actual')
                    ->label('Número Actual')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('fecha_limite_emision')
                    ->label('Fecha Límite')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('activo')
                    ->options([
                        true => 'Activo',
                        false => 'Inactivo',
                    ])
                    ->label('Estado'),
                SelectFilter::make('empresa_id')
                    ->label('Empresa')
                    ->options(Empresa::pluck('nombre', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->hasRole('root')), // <-- CAMBIO AQUÍ: Filtro visible solo para rol 'root'
                Tables\Filters\TrashedFilter::make(), // Filtro para registros eliminados (soft deletes)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Agregamos la acción de ver
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Agregamos la acción de eliminar
                Tables\Actions\RestoreAction::make(), // Acción para restaurar soft deletes
                Tables\Actions\ForceDeleteAction::make(), // Acción para eliminar permanentemente soft deletes
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Puedes añadir relation managers aquí si los necesitas
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCais::route('/'),
            'create' => Pages\CreateCai::route('/create'),
            //'view' => Pages\ViewCai::route('/{record}'), // Página de vista
            'edit' => Pages\EditCai::route('/{record}/edit'),
        ];
    }

    // Método para definir el query base para el recurso
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

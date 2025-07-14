<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedoresResource\Pages;
use App\Models\Proveedores;
use Filament\Facades\Filament;
use App\Models\Paises;
use App\Models\Departamento;
use App\Models\Municipio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProveedoresResource extends Resource
{
    protected static ?string $model = Proveedores::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Proveedores';
    protected static ?string $pluralModelLabel = 'Proveedores';
    protected static ?string $modelLabel = 'Proveedor';
    protected static ?string $navigationGroup = 'Comercial';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\TextInput::make('nombre_proveedor')
                ->label('Nombre del Proveedor')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('telefono')
                ->tel()
                ->required()
                ->maxLength(20),

            Forms\Components\TextInput::make('rtn')
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
                ->disabled(fn () => true) // visualmente no editable
                ->dehydrated(true)        // asegura que se envíe el valor en el form
                ->suffix(null),    

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


            // Eliminamos los campos created_by, updated_by, deleted_by del formulario
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_proveedor')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rtn')
                    ->label('RTN')
                    ->searchable(),
    

                Tables\Columns\TextColumn::make('municipio.nombre_municipio')
                    ->label('Municipio')
                    ->searchable()
                    ->sortable(),
                
                

                Tables\Columns\TextColumn::make('persona_contacto')
                    ->label('Contacto')
                    ->searchable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Buscar'),
            ])
            ->actions([
                EditAction::make()
                    ->label('Editar'),
                Tables\Actions\ViewAction::make()->label('Ver'),
                DeleteAction::make()
                    ->label('Eliminar'),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label('Eliminar'),
                Tables\Actions\RestoreBulkAction::make()->label('Restaurar'),
                Tables\Actions\ForceDeleteBulkAction::make()->label('Eliminar Definitivamente'),
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
            'index' => Pages\ListProveedores::route('/'),
            'create' => Pages\CreateProveedores::route('/create'),
            'edit' => Pages\EditProveedores::route('/{record}/edit'),
            'view' => Pages\ViewProveedor::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['municipio.departamento.pais', 'empresa'])
            ->withoutGlobalScopes([
                SoftDeletes::class,
        ]);
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpresaResource\Pages;
use App\Filament\Resources\EmpresaResource\RelationManagers;
use App\Models\Empresa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';


    //Cambio Jessuri

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Card::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('rtn')
                                ->label('RTN')
                                ->placeholder('Ej. 08011985123960')
                                ->required()
                                ->maxLength(20)
                                ->unique(ignoreRecord: true),
                            TextInput::make('nombre')
                                ->label('Nombre de la empresa')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->validationMessages([
                                    'unique' => 'El nombre de la empresa ya está registrado.',
                                ]),

                            Select::make('pais_id')
                                ->label('País')
                                ->options(\App\Models\Paises::pluck('nombre_pais', 'id'))
                                ->searchable()
                                ->reactive()
                                ->required(),

                            Select::make('departamento_id')
                                ->label('Departamento')
                                ->reactive()
                                ->searchable()
                                ->required()
                                ->options(function (callable $get) {
                                    $paisId = $get('pais_id');
                                    if (!$paisId) return [];
                                    return \App\Models\Departamento::where('pais_id', $paisId)
                                        ->pluck('nombre_departamento', 'id');
                                }),

                            Select::make('municipio_id')
                                ->label('Municipio')
                                ->required()
                                ->searchable()
                                ->options(function (callable $get) {
                                    $departamentoId = $get('departamento_id');
                                    if (!$departamentoId) return [];
                                    return \App\Models\Municipio::where('departamento_id', $departamentoId)
                                        ->pluck('nombre_municipio', 'id');
                                }),


                            TextInput::make('telefono')
                                ->label('Teléfono')
                                ->placeholder('Ej. +504 9999-9999')
                                ->tel()
                                ->maxLength(20)
                                ->rules(['regex:/^[0-9+\s-]{8,20}$/'])
                                ->validationMessages([
                                    'regex' => 'El formato del teléfono no es válido.',
                                ]),

                            Forms\Components\Textarea::make('direccion')
                                ->label('Dirección')
                                ->placeholder('Ej. Barrio El Centro, Ave. Principal #123')
                                ->required()
                                ->autosize()
                                ->rows(4)
                                ->maxLength(200)
                                ->rules(['string', 'min:5']),

                            FileUpload::make('fotos')
                                ->label('Fotos de la empresa')
                                ->multiple()
                                ->directory('empresas/fotos')
                                ->image()
                                ->maxSize(2048)
                                ->enableDownload()
                                ->enableOpen()
                                ->reorderable(),
                            
                        ]),
                ]),
        ]);
}
    public static function table(Table $table): Table
    {
        // cambio jessuri: Configura las columnas que se mostrarán en la tabla de empresas en el panel Filament.
        // Muestra nombre, municipio (relación), dirección, teléfono, RTN, fecha de creación y de última edición.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('municipio.nombre_municipio')
                    ->label('Municipio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono'),
                Tables\Columns\TextColumn::make('rtn')
                    ->label('RTN')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(), 
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmpresas::route('/'),
            'create' => Pages\CreateEmpresa::route('/create'),
            'edit' => Pages\EditEmpresa::route('/{record}/edit'),
            'view' => Pages\ViewEmpresa::route('/{record}'), 
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpresaResource\Pages;
use App\Filament\Resources\EmpresaResource\RelationManagers;
use App\Models\Empresa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';


    //Cambio Jessuri

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre de la empresa')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'El nombre de la empresa ya está registrado.',
                    ]),
                Forms\Components\Select::make('municipio_id')
                    ->label('Municipio')
                    ->relationship('municipio', 'nombre_municipio')
                    ->required(),
                Forms\Components\TextInput::make('direccion')
                    ->label('Dirección')
                    ->required()
                    ->maxLength(200),
                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono')
                    ->maxLength(20),
                Forms\Components\TextInput::make('rtn')
                    ->label('RTN')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'El RTN ya está registrado.',
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
                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
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
        ];
    }
}

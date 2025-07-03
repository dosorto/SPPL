<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonaResource\Pages;
use App\Filament\Resources\PersonaResource\RelationManagers;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonaResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Subir el campo 'dni' al inicio
                Forms\Components\TextInput::make('dni')
                    ->label('DNI')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('primer_nombre')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('segundo_nombre')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('primer_apellido')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('segundo_apellido')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('direccion')
                    ->required()
                    ->columnSpanFull(),
                // Usar Select para relaciones municipio y país
                // Mostrar primero el país
                Forms\Components\Select::make('pais_id')
                    ->label('País')
                    ->options(\App\Models\Paises::pluck('nombre_pais', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('departamento_id')
                    ->label('Departamento')
                    ->options(function (callable $get) {
                        $paisId = $get('pais_id');
                        if (!$paisId) return [];
                        return \App\Models\Departamento::where('pais_id', $paisId)->pluck('nombre_departamento', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->disabled(fn (callable $get) => !$get('pais_id')),
                // Municipio depende del departamento seleccionado
                Forms\Components\Select::make('municipio_id')
                    ->label('Municipio')
                    ->options(function (callable $get) {
                        $departamentoId = $get('departamento_id');
                        if (!$departamentoId) return [];
                        return \App\Models\Municipio::where('departamento_id', $departamentoId)->pluck('nombre_municipio', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn (callable $get) => !$get('departamento_id')),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\Select::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'MASCULINO' => 'Masculino',
                        'FEMENINO' => 'Femenino',
                        'OTRO' => 'Otro',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->required(),
                Forms\Components\FileUpload::make('fotografia')
                    ->image()
                    ->directory('fotografias')
                    ->nullable(),
                // Agregar campo para seleccionar empresa
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->options(\App\Models\Empresa::pluck('nombre', 'id'))
                    ->searchable()
                    ->nullable()
                    ->helperText('Seleccione la empresa asociada a la persona.'),
                // Ocultar campos de logs en el formulario
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dni')
                    ->searchable(),
                Tables\Columns\TextColumn::make('primer_nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('segundo_nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('primer_apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('segundo_apellido')
                    ->searchable(),
                // Mostrar nombre de municipio e país em vez de ID
                Tables\Columns\TextColumn::make('municipio.nombre_municipio')
                    ->label('Municipio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pais.nombre_pais')
                    ->label('País')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sexo'),
                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('fotografia')
                    ->label('Fotografía')
                    ->disk('public')
                    ->circular()
                    ->visible(fn ($record) => $record && filled(optional($record)->fotografia)),
                // Ocultar logs y timestamps por defecto
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('dni')
                    ->form([
                        Forms\Components\TextInput::make('dni')->label('DNI'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['dni']) {
                            $query->where('dni', 'like', '%'.$data['dni'].'%');
                        }
                    }),
                Tables\Filters\Filter::make('primer_nombre')
                    ->form([
                        Forms\Components\TextInput::make('primer_nombre')->label('Nombre'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['primer_nombre']) {
                            $query->where('primer_nombre', 'like', '%'.$data['primer_nombre'].'%');
                        }
                    }),
                Tables\Filters\SelectFilter::make('empresa_id')
                    ->label('Empresa')
                    ->options(\App\Models\Empresa::pluck('nombre', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()->modal(true),
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
            'index' => Pages\ListPersonas::route('/'),
            'view' => Pages\ViewPersona::route('/{record}'),
        ];
    }
}

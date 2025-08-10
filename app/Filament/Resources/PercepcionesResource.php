<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PercepcionesResource\Pages;
use App\Filament\Resources\PercepcionesResource\RelationManagers;
use App\Models\Percepciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

class PercepcionesResource extends Resource
{
    protected static ?string $model = Percepciones::class;
    protected static ?string $navigationGroup = 'Nominas';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'nombre')
                    ->default(fn () => Filament::auth()->user()?->empresa_id)
                    ->hidden()
                    ->dehydrated(true),

                Forms\Components\TextInput::make('percepcion')
                    ->label('Nombre de la percepción')
                    ->required(),

                Forms\Components\Select::make('tipo_valor')
                    ->label('Tipo de valor')
                    ->options([
                        'porcentaje' => 'Porcentaje',
                        'monto' => 'Monto',
                    ])
                    ->default('porcentaje')
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->required()
                    ->suffix(fn (Get $get) => $get('tipo_valor') === 'porcentaje' ? '%' : ($get('tipo_valor') === 'monto' ? 'L' : ''))
                    ->helperText('Ejemplo: 5 = 5% ó 500 = L500'),
                    
                Forms\Components\Toggle::make('depende_cantidad')
                    ->label('Depende de una cantidad')
                    ->default(false)
                    ->reactive(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('percepcion')
                    ->label('Percepción')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->tipo_valor === 'porcentaje') {
                            $valor = rtrim(rtrim($state, '0'), '.');
                            return $valor . '%';
                        }
                        return 'L ' . number_format($state, 2);
                    }),

                Tables\Columns\TextColumn::make('tipo_valor')
                    ->label('Tipo'),

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
            'index' => Pages\ListPercepciones::route('/'),
            'create' => Pages\CreatePercepciones::route('/create'),
            'edit' => Pages\EditPercepciones::route('/{record}/edit'),
        ];
    }
}

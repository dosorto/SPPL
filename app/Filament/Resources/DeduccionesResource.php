<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeduccionesResource\Pages;
use App\Models\Deducciones;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeduccionesResource extends Resource
{
    protected static ?string $model = Deducciones::class;
    protected static ?string $navigationGroup = 'Nominas';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('deduccion')
                    ->label('Nombre de la deducción')
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

                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'nombre')
                    ->default(fn () => Filament::auth()->user()?->empresa_id)
                    ->hidden()
                    ->required()
                    ->dehydrated(true),

                TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->required()
                    ->suffix(fn (Get $get) => $get('tipo_valor') === 'porcentaje' ? '%' : 'L')
                    ->helperText('Ejemplo: 5 = 5% ó 500 = L500'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('deduccion')
                    ->label('Deducción'),

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
                Tables\Actions\ViewAction::make(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeducciones::route('/'),
            'create' => Pages\CreateDeducciones::route('/create'),
            'edit' => Pages\EditDeducciones::route('/{record}/edit'),
        ];
    }

}

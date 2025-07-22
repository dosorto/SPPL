<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeduccionesResource\Pages;
use App\Filament\Resources\DeduccionesResource\RelationManagers;
use App\Models\Deducciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

class DeduccionesResource extends Resource
{
    protected static ?string $model = Deducciones::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

            Forms\Components\TextInput::make('valor')
                ->label('Valor')
                ->numeric()
                ->required()
                ->suffix(function (Get $get) {
                    return $get('tipo_valor') === 'porcentaje' ? '%' : 'L';
                })
                ->helperText('Ejemplo: 5 = 5% ó 500 = L500'),
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('deduccion')->label('Deducción'),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->tipo_valor === 'porcentaje'
                            ? $state . '%'
                            : 'L ' . number_format($state, 2);
            }),

            Tables\Columns\TextColumn::make('tipo_valor')->label('Tipo'),

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
        return [
            //
        ];
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

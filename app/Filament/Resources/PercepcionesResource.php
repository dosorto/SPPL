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

class PercepcionesResource extends Resource
{
    protected static ?string $model = Percepciones::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('percepcion')
                    ->label('Nombre de la percepción')
                    ->required()
                    ->maxLength(255),

                \Filament\Forms\Components\TextInput::make('valor')
                    ->label('Valor (Lempiras)')
                    ->numeric()
                    ->required()
                    ->minValue(0),
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

                \Filament\Tables\Columns\TextColumn::make('valor')
                    ->label('Valor (Lempiras)')
                    ->money('HNL', true)
                    ->sortable(),

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

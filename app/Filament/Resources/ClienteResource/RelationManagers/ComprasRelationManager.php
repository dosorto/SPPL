<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class ComprasRelationManager extends RelationManager
{

    public static function getRelationshipName(): string
    {
        return 'facturas';
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->heading('Historial de compras')
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('fecha_factura')->label('Fecha de Factura'),
                TextColumn::make('empresa.nombre')->label('Empresa'),
                TextColumn::make('created_at')->dateTime()->label('Creada'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}

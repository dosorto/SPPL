<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CajaAperturaResource\Pages;
use App\Models\CajaApertura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\FacturaResource;
use Illuminate\Support\Facades\Auth;

class CajaAperturaResource extends Resource
{
    protected static ?string $model = CajaApertura::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Ventas';
           
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // El campo 'user_id' ya no es visible, se asigna automáticamente.
                
                Forms\Components\TextInput::make('monto_inicial')
                    ->required()
                    ->numeric()
                    ->prefix('L')
                    ->default(2000.00), // Valor por defecto en el formulario
                
                // Los siguientes campos no tienen sentido al crear, solo al editar o ver.
                Forms\Components\TextInput::make('monto_final_calculado')
                    ->numeric()
                    ->prefix('L')
                    ->disabled() // Deshabilitado para que no se pueda editar
                    ->visibleOn('edit'), // Solo visible en la página de edición
                
                Forms\Components\DateTimePicker::make('fecha_cierre')
                    ->disabled()
                    ->visibleOn('edit'),
                
                // El campo 'estado' ya no es visible, se asigna automáticamente.
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('monto_inicial')
                    ->money('LPS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_apertura')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_cierre')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('N/A'), // Texto si está vacío
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ABIERTA' => 'success',
                        'CERRADA' => 'danger',
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                
                // Acción para ir a facturar (solo cajas abiertas)
                Tables\Actions\Action::make('ir_a_facturar')
                    ->label('Ir a Facturar')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn (CajaApertura $record): bool => $record->estado === 'ABIERTA')
                    ->action(function (CajaApertura $record) {
                        session(['apertura_id' => $record->id]);
                        return redirect(FacturaResource::getUrl('generar-factura'));
                    }),
                
                // 👇 NUEVA ACCIÓN PARA VER REPORTE DE CAJA CERRADA
                Tables\Actions\Action::make('ver_reporte')
                    ->label('Ver Reporte')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->visible(fn (CajaApertura $record): bool => $record->estado === 'CERRADA')
                    ->url(fn (CajaApertura $record): string => static::getUrl('reporte', ['record' => $record->id])),
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
            'index' => Pages\ListCajaAperturas::route('/'),
            'create' => Pages\CreateCajaApertura::route('/create'),
            'edit' => Pages\EditCajaApertura::route('/{record}/edit'),
            'reporte' => Pages\ReporteCajaApertura::route('/{record}/reporte'),
        ];
    }
}
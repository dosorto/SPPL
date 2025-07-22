<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetalleNominaResource\Pages;
use App\Filament\Resources\DetalleNominaResource\RelationManagers;
use App\Models\DetalleNominas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\NumberColumn;

class DetalleNominaResource extends Resource
{
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        if (auth()->check()) {
            // Aseguramos que exista empresa_id en la consulta
            $query->where('empresa_id', auth()->user()->empresa_id);
            
            // Agregamos logging para depuración
            \Illuminate\Support\Facades\Log::info('Filtrando DetalleNominas', [
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
            ]);
        }
        return $query;
    }
    protected static ?string $model = DetalleNominas::class;

    protected static ?string $navigationLabel = 'Historial de Pagos';

    protected static ?string $modelLabel = 'Historial de Pago';
    

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('nomina_id')
                    ->label('Nómina')
                    ->relationship('nomina', 'folio')
                    ->required(),

                \Filament\Forms\Components\Select::make('empleado_id')
                    ->label('Empleado')
                    ->relationship('empleado', 'nombre_completo')
                    ->required(),
                    
                \Filament\Forms\Components\Hidden::make('empresa_id')
                    ->default(fn() => auth()->user()->empresa_id),

                \Filament\Forms\Components\TextInput::make('sueldo_bruto')
                    ->label('Sueldo bruto')
                    ->numeric()
                    ->required(),

                \Filament\Forms\Components\TextInput::make('deducciones')
                    ->label('Deducciones')
                    ->numeric()
                    ->required(),

                \Filament\Forms\Components\TextInput::make('percepciones')
                    ->label('Percepciones')
                    ->numeric()
                    ->required(),

                \Filament\Forms\Components\TextInput::make('sueldo_neto')
                    ->label('Sueldo neto')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('empleado.nombre_completo')->label('Empleado'),
                \Filament\Tables\Columns\TextColumn::make('sueldo_bruto')->label('Sueldo Bruto'),
                \Filament\Tables\Columns\TextColumn::make('deducciones')->label('Deducciones'),
                \Filament\Tables\Columns\TextColumn::make('percepciones')->label('Percepciones'),
                \Filament\Tables\Columns\TextColumn::make('sueldo_neto')->label('Sueldo Neto'),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
            ])
            ->headerActions([]); // Oculta el botón de crear
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetalleNominas::route('/'),
            'edit' => Pages\EditDetalleNomina::route('/{record}/edit'),
            'view' => Pages\ViewDetalleNomina::route('/{record}'),
        ];
    }
    
    // Ocultar completamente la opción de crear nuevos registros
    public static function canCreate(): bool
    {
        return false;
    }
}

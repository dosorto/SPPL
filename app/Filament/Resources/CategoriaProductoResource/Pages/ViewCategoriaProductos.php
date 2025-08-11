<?php

namespace App\Filament\Resources\CategoriaProductoResource\Pages;

use App\Filament\Resources\CategoriaProductoResource;
use App\Filament\Resources\ProductosResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;

class ViewCategoriaProductos extends ViewRecord
{
    protected static string $resource = CategoriaProductoResource::class;

    protected static ?string $title = 'Ver Categoría de Producto';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('nombre')
                    ->label('Nombre de la Categoría')
                    ->weight('bold'),
                TextEntry::make('subcategorias')
                    ->label('Subcategorías')
                    ->formatStateUsing(fn ($record) => $record->subcategorias->pluck('nombre')->join(', ') ?: 'Sin subcategorías')
                    ->color('gray'),
                TextEntry::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->color('gray'),
            ])
            ->columns(1);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->tooltip('Modificar esta categoría'),
                Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Categoría')
                    ->modalDescription('¿Estás seguro de que quieres eliminar esta categoría? Esto también eliminará todas sus subcategorías.')
                    ->tooltip('Eliminar esta categoría'),
                Actions\Action::make('create_product')
                    ->label('Registrar Producto')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->url(fn ($record): string => ProductosResource::getUrl('create', [
                        'categoria_id' => $record->id,
                        'subcategoria_id' => $record->subcategorias->first()->id ?? null,
                    ]))
                    ->tooltip('Crear un nuevo producto en esta categoría'),
            ])
                ->label('Acciones')
                ->button()
                ->outlined()
                ->dropdown(true),
        ];
    }
}
<?php

namespace App\Filament\Resources\ProductosResource\Pages;

use App\Filament\Resources\ProductosResource;
use Illuminate\Support\HtmlString;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;

class ViewProductos extends ViewRecord
{
    protected static string $resource = ProductosResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $this->record->load('fotosRelacion');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->columns(2);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Información Básica')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Placeholder::make('nombre')
                        ->label('Nombre del producto')
                        ->content(fn () => $this->record->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-lg font-semibold text-gray-800']),

                    Placeholder::make('unidadDeMedida')
                        ->label('Unidad de medida')
                        ->content(fn () => $this->record->unidadDeMedida?->nombre ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('sku')
                        ->label('SKU')
                        ->content(fn () => $this->record->sku ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('codigo')
                        ->label('Código de barras')
                        ->content(fn () => $this->record->codigo ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Detalles')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Placeholder::make('isv')
                        ->label('ISV')
                        ->content(fn () => $this->record->isv ? $this->record->isv . '%' : 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('descripcion_corta')
                        ->label('Descripción corta')
                        ->content(fn () => $this->record->descripcion_corta ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('descripcion')
                        ->label('Descripción larga')
                        ->content(fn () => $this->record->descripcion ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),

                    Placeholder::make('color')
                        ->label('Color')
                        ->content(fn () => $this->record->color ?? 'N/A')
                        ->extraAttributes(['class' => 'text-gray-600']),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Galería de Imágenes')
                ->icon('heroicon-o-photo')
                ->schema([
                    Grid::make()
                        ->columns(3)
                        ->schema(
                            $this->record->fotosRelacion?->map(function ($foto) {
                                return Placeholder::make('foto_' . $foto->id)
                                    ->label(false)
                                    ->content(new HtmlString(
                                        "<img src='" . asset("storage/" . $foto->url) . "' alt='Imagen' style='border-radius: 10px; max-width: 100%; max-height: 200px;' />"
                                    ))
                                    ->extraAttributes(['class' => 'p-2']);
                            })->toArray() ?? []
                        ),
                ])
                ->collapsible()

        ];
    }
}

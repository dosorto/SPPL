<?php

namespace App\Filament\Resources\ProductosResource\Pages;

use App\Filament\Resources\ProductosResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;

class ViewProductos extends ViewRecord
{
    protected static string $resource = ProductosResource::class;

    // Sobrescribe el formulario para asegurar que no use el wizard
    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->columns(2); // Diseño en dos columnas para todo el formulario
    }

    // Define el esquema personalizado para la vista
    protected function getFormSchema(): array
    {
        return [
            // Sección para información básica
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

            // Sección para detalles fiscales y descripción
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

            // Sección para la galería de imágenes
            Section::make('Galería de Imágenes')
                ->icon('heroicon-o-photo')
                ->schema([
                    FileUpload::make('fotos')
                        ->label('')
                        ->multiple()
                        ->directory('productos')
                        ->image()
                        ->disabled()
                        ->imagePreviewHeight('200')
                        ->extraAttributes(['class' => 'bg-gray-100 p-4 rounded-lg'])
                        ->default(fn () => $this->record->fotos)
                        ->enableOpen()
                        ->panelLayout('grid'), // Mostrar imágenes en una cuadrícula
                ])
                ->collapsible(),
        ];
    }
}
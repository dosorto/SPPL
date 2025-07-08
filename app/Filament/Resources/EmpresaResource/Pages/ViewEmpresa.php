<?php

namespace App\Filament\Resources\EmpresaResource\Pages;

use App\Filament\Resources\EmpresaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Pages\Actions\EditAction;

class ViewEmpresa extends ViewRecord
{
    protected static string $resource = EmpresaResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->columns(2);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Información de la Empresa')
                ->icon('heroicon-o-building-office')
                ->schema([
                    Placeholder::make('nombre')
                        ->label('Nombre')
                        ->content(fn () => $this->record->nombre ?? 'N/A'),

                    Placeholder::make('rtn')
                        ->label('RTN')
                        ->content(fn () => $this->record->rtn ?? 'N/A'),

                    Placeholder::make('telefono')
                        ->label('Teléfono')
                        ->content(fn () => $this->record->telefono ?? 'N/A'),

                    Placeholder::make('pais')
                        ->label('País')
                        ->content(fn () => $this->record->pais?->nombre_pais ?? 'N/A'),

                    Placeholder::make('departamento')
                        ->label('Departamento')
                        ->content(fn () => $this->record->departamento?->nombre_departamento ?? 'N/A'),

                    Placeholder::make('municipio')
                        ->label('Municipio')
                        ->content(fn () => $this->record->municipio?->nombre_municipio ?? 'N/A'),

                    Placeholder::make('direccion')
                        ->label('Dirección')
                        ->content(fn () => $this->record->direccion ?? 'N/A'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Fotos de la empresa')
                ->icon('heroicon-o-photo')
                ->schema([
                    \Filament\Forms\Components\FileUpload::make('fotos')
                        ->label('Fotos')
                        ->multiple()
                        ->directory('empresas/fotos')
                        ->image()
                        ->disabled()
                        ->imagePreviewHeight('200')
                        ->enableOpen()
                        ->panelLayout('grid')
                        ->default(fn () => $this->record->fotos),
                ])
                ->collapsible()
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

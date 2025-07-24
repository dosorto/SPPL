<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Illuminate\Contracts\View\View;

class ViewCliente extends ViewRecord
{
    protected static string $resource = ClienteResource::class;
    
    protected function getHeaderWidgets(): array
    {
        return [];
    }
    
    public function getFooterWidgets(): array
    {
        return [];
    }
    
    public function getHeading(): string
    {
        $record = $this->getRecord();
        return 'Cliente: ' . optional($record->persona)->primer_nombre . ' ' . optional($record->persona)->primer_apellido;
    }
    
    public function getHeader(): ?View
    {
        $record = $this->getRecord();
        $persona = optional($record->persona);
        
        return view('filament.resources.cliente-resource.pages.cliente-header', [
            'record' => $record,
            'persona' => $persona
        ]);
    }

    // Use the getViewForm() method from the ClienteResource class instead of defining a custom form
    public function getForm(string $name = 'form'): ?\Filament\Forms\Form
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        
        return $resource::getViewForm($this->makeForm())
            ->model($record)
            ->statePath('data');
    }
}

<?php

namespace App\Filament\Resources\PersonaResource\Pages;

use App\Filament\Resources\PersonaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Components;

class ViewPersona extends ViewRecord
{
    protected static string $resource = PersonaResource::class;
    
    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        $record = $this->getRecord();
        
        return view('filament.resources.persona-resource.pages.persona-header', [
            'record' => $record
        ]);
    }
    
    // Use the getViewForm() method from the PersonaResource class
    public function getForm(string $name = 'form'): ?\Filament\Forms\Form
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        
        return $resource::getViewForm($this->makeForm())
            ->model($record)
            ->statePath('data');
    }
}

<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;

class ViewCliente extends ViewRecord
{
    protected static string $resource = ClienteResource::class;

    public function getForm(string $name = 'form'): ?\Filament\Forms\Form
    {
        return $this->makeForm()
            ->schema([
                Forms\Components\TextInput::make('numero_cliente')->label('Número de Cliente')->disabled(),
                Forms\Components\TextInput::make('rtn')->label('RTN')->disabled(),
                Forms\Components\TextInput::make('empresa.nombre')->label('Empresa')->disabled(),
                Forms\Components\TextInput::make('created_at')->label('Fecha de creación')->disabled(),
                Forms\Components\TextInput::make('updated_at')->label('Última actualización')->disabled(),
            ])
            ->model($this->getRecord())
            ->statePath('data');
    }
}

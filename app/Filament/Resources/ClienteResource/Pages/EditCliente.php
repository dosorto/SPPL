<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getForm(string $name = 'form'): ?\Filament\Forms\Form
    {
        return $this->makeForm()
            ->schema([
                Forms\Components\TextInput::make('numero_cliente')->label('Número de Cliente')->required(),
                Forms\Components\TextInput::make('rtn')->label('RTN')->required(),
                Forms\Components\Select::make('empresa_id')->label('Empresa')
                    ->options(\App\Models\Empresa::pluck('nombre', 'id')),
                Forms\Components\TextInput::make('persona.nombre')->label('Nombre Completo')->disabled(),
                Forms\Components\TextInput::make('persona.dni')->label('DNI')->required(),
                Forms\Components\TextInput::make('persona.telefono')->label('Teléfono'),
                // Puedes agregar más campos de persona aquí si lo deseas
            ])
            ->model($this->getRecord())
            ->statePath('data');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

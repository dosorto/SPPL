<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si no se selecciona empresa, asigna la de entorno
        if (empty($data['empresa_id'])) {
            $data['empresa_id'] = env('EMPRESA_ID');
        }
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Extraer datos de persona
        $personaData = $data['persona'];
        // Crear la persona
        $persona = \App\Models\Persona::create($personaData);
        // Crear el cliente y asociar la persona y la empresa de la persona
        $clienteData = $data;
        $clienteData['persona_id'] = $persona->id;
        $clienteData['empresa_id'] = $persona->empresa_id; // Siempre igual a la de persona
        // Generar número de cliente automáticamente
        $ultimo = \App\Models\Cliente::max('id') ?? 0;
        $clienteData['numero_cliente'] = 'C-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
        unset($clienteData['persona']);
        return static::getModel()::create($clienteData);
    }
}

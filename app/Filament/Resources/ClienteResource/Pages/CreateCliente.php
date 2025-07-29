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
        
        // Verificar si es una persona autocompletada
        if (isset($data['persona_autocompletada']) && $data['persona_autocompletada']) {
            // Buscar la persona existente por DNI
            $persona = \App\Models\Persona::where('dni', $personaData['dni'])->first();
            
            if (!$persona) {
                throw new \Exception('No se encontró la persona autocompletada. Por favor, intente nuevamente.');
            }
        } else {
            // Crear nueva persona
            $persona = \App\Models\Persona::create($personaData);
        }

        // Crear el cliente y asociar la persona
        $clienteData = $data;
        $clienteData['persona_id'] = $persona->id;
        $clienteData['empresa_id'] = $data['empresa_id'] ?? env('EMPRESA_ID', 1);
        
        // Generar número de cliente único
        $ultimo = \App\Models\Cliente::max('id') ?? 0;
        $clienteData['numero_cliente'] = 'C-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
        
        // Remover datos que no pertenecen a la tabla clientes
        unset($clienteData['persona']);
        unset($clienteData['persona_autocompletada']);

        $cliente = static::getModel()::create($clienteData);

        if (!$cliente || !$cliente->id) {
            throw new \Exception('No se pudo crear el cliente.');
        }

        return $cliente;
    }
}

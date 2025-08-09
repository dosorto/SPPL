<?php

namespace App\Observers;

use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Cliente;
use App\Models\CategoriaCliente;
use Illuminate\Support\Facades\DB;

class EmpresaObserver
{
    public function created(Empresa $empresa): void
    {
        DB::transaction(function () use ($empresa) {

            // Personas globales (sin tenant). No seteamos empresa_id aquí.
            $personaCF = Persona::firstOrCreate(
                ['dni' => '0000000000000'],
                [
                    'primer_nombre'    => 'Consumidor',
                    'primer_apellido'  => 'Final',
                    'tipo_persona'     => 'natural',
                    'sexo'             => 'MASCULINO',
                    'fecha_nacimiento' => now(),
                    'direccion'        => 'Ciudad',
                    'telefono'         => '0000-0000',
                ]
            );

            $personaMayorista = Persona::firstOrCreate(
                ['dni' => '1111111111111'],
                [
                    'primer_nombre'    => 'Consumidor',
                    'primer_apellido'  => 'Mayorista',
                    'tipo_persona'     => 'natural',
                    'sexo'             => 'MASCULINO',
                    'fecha_nacimiento' => now(),
                    'direccion'        => 'Ciudad',
                    'telefono'         => '0000-0000',
                ]
            );

            // Si se acaban de crear, puedes opcionalmente setear ubicación inicial.
            if ($personaCF->wasRecentlyCreated) {
                $personaCF->fill([
                    'pais_id'         => $empresa->pais_id,
                    'departamento_id' => $empresa->departamento_id,
                    'municipio_id'    => $empresa->municipio_id,
                ])->save();
            }

            if ($personaMayorista->wasRecentlyCreated) {
                $personaMayorista->fill([
                    'pais_id'         => $empresa->pais_id,
                    'departamento_id' => $empresa->departamento_id,
                    'municipio_id'    => $empresa->municipio_id,
                ])->save();
            }

            // Categorías GLOBALes (categorias_clientes no tiene empresa_id).
            // Si no existen, NO es obligatorio crearlas; 'categoria_cliente_id' puede quedar null.
            $catCF = CategoriaCliente::where('nombre', 'Consumidor Final')->first();
            $catMayorista = CategoriaCliente::where('nombre', 'Mayorista')->first();

            // Generador robusto para numero_cliente (único global por tu schema).
            $genNumero = function (): string {
                do {
                    // usa el próximo id + padding, o genera como prefieras
                    $next = (Cliente::max('id') ?? 0) + 1;
                    $numero = str_pad((string)$next, 6, '0', STR_PAD_LEFT);
                } while (Cliente::where('numero_cliente', $numero)->exists());

                return $numero;
            };

            // Cliente Consumidor Final para esta empresa
            Cliente::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'persona_id' => $personaCF->id,
                ],
                [
                    'numero_cliente'       => $genNumero(),
                    'rtn'                  => '00000000000000',
                    'categoria_cliente_id' => $catCF?->id, // puede quedar null
                ]
            );

            // Cliente Consumidor Mayorista para esta empresa
            Cliente::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'persona_id' => $personaMayorista->id,
                ],
                [
                    'numero_cliente'       => $genNumero(),
                    'rtn'                  => null,
                    'categoria_cliente_id' => $catMayorista?->id, // puede quedar null
                ]
            );
        });
    }
}

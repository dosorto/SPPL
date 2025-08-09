<?php

namespace App\Observers;

use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Cliente;
use App\Models\CategoriaCliente;
use Illuminate\Support\Facades\DB;

class EmpresaObserver
{
    /**
     * Handle the Empresa "created" event.
     *
     * @param  \App\Models\Empresa  $empresa
     * @return void
     */
    public function created(Empresa $empresa): void
    {
        DB::transaction(function () use ($empresa) {

            // Obtener o crear la persona de Consumidor Final
            // Asegurarse de incluir los IDs de ubicación directamente en la creación
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
                    // ¡IMPORTANTE! Asegurarse de que estos campos obligatorios existan y tengan un valor.
                    // Usamos los IDs de la empresa que se acaba de crear.
                    'pais_id'          => $empresa->pais_id,
                    'departamento_id'  => $empresa->departamento_id,
                    'municipio_id'     => $empresa->municipio_id,
                ]
            );

            // Obtener o crear la persona de Consumidor Mayorista
            // Asegurarse de incluir los IDs de ubicación directamente en la creación
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
                    // ¡IMPORTANTE! Asegurarse de que estos campos obligatorios existan y tengan un valor.
                    // Usamos los IDs de la empresa que se acaba de crear.
                    'pais_id'          => $empresa->pais_id,
                    'departamento_id'  => $empresa->departamento_id,
                    'municipio_id'     => $empresa->municipio_id,
                ]
            );

            // Las siguientes líneas de fill()->save() para personaCF y personaMayorista
            // ya no son estrictamente necesarias para asignar los IDs de ubicación si se pasaron
            // en firstOrCreate. Sin embargo, no causan daño y podrían ser útiles si
            // decides asignar otros atributos *después* de la creación inicial
            // (aunque en este caso, es mejor pasarlos todos en la primera creación).
            // Las mantenemos por si acaso hay alguna otra lógica en el futuro.
            if ($personaCF->wasRecentlyCreated) {
                // Aquí podrías añadir cualquier otra lógica que dependa de que la persona se haya creado en esta llamada.
            }

            if ($personaMayorista->wasRecentlyCreated) {
                // Aquí podrías añadir cualquier otra lógica que dependa de que la persona se haya creado en esta llamada.
            }

            // Categorías GLOBALes (categorias_clientes no tiene empresa_id).
            // Si no existen, NO es obligatorio crearlas; 'categoria_cliente_id' puede quedar null.
            $catCF = CategoriaCliente::where('nombre', 'Consumidor Final')->first();
            $catMayorista = CategoriaCliente::where('nombre', 'Mayorista')->first();

            // Generador robusto para numero_cliente (único global por tu schema).
            $genNumero = function (): string {
                do {
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

    /**
     * Handle the Empresa "updated" event.
     *
     * @param  \App\Models\Empresa  $empresa
     * @return void
     */
    public function updated(Empresa $empresa): void
    {
        //
    }

    /**
     * Handle the Empresa "deleted" event.
     *
     * @param  \App\Models\Empresa  $empresa
     * @return void
     */
    public function deleted(Empresa $empresa): void
    {
        //
    }

    /**
     * Handle the Empresa "restored" event.
     *
     * @param  \App\Models\Empresa  $empresa
     * @return void
     */
    public function restored(Empresa $empresa): void
    {
        //
    }

    /**
     * Handle the Empresa "force deleted" event.
     *
     * @param  \App\Models\Empresa  $empresa
     * @return void
     */
    public function forceDeleted(Empresa $empresa): void
    {
        //
    }
}


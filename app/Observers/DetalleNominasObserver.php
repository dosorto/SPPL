<?php

namespace App\Observers;

use App\Models\DetalleNominas;
use Illuminate\Support\Facades\Auth;

class DetalleNominasObserver
{
    /**
     * Handle the DetalleNominas "creating" event.
     */
    public function creating(DetalleNominas $detalleNomina): void
    {
        // Siempre intentamos asignar el empresa_id, incluso si ya tiene uno
        // Primera prioridad: Obtener de la nómina asociada
        if (!empty($detalleNomina->nomina_id)) {
            $nomina = \App\Models\Nominas::find($detalleNomina->nomina_id);
            if ($nomina && !empty($nomina->empresa_id)) {
                $detalleNomina->empresa_id = $nomina->empresa_id;
                return; // Si ya obtenemos empresa_id de la nómina, terminamos
            }
        }
        
        // Segunda prioridad: Usar el empresa_id del usuario autenticado
        if (Auth::check() && !empty(Auth::user()->empresa_id)) {
            $detalleNomina->empresa_id = Auth::user()->empresa_id;
            return;
        }
        
        // Mensaje de log si no se pudo asignar empresa_id
        \Illuminate\Support\Facades\Log::warning('No se pudo asignar empresa_id a DetalleNominas', [
            'nomina_id' => $detalleNomina->nomina_id,
            'empleado_id' => $detalleNomina->empleado_id,
        ]);
    }

    /**
     * Handle the DetalleNominas "updating" event.
     */
    public function updating(DetalleNominas $detalleNomina): void
    {
        // Misma lógica que en creating para mantener consistencia
        // Primera prioridad: Obtener de la nómina asociada
        if (!empty($detalleNomina->nomina_id)) {
            $nomina = \App\Models\Nominas::find($detalleNomina->nomina_id);
            if ($nomina && !empty($nomina->empresa_id)) {
                $detalleNomina->empresa_id = $nomina->empresa_id;
                return; // Si ya obtenemos empresa_id de la nómina, terminamos
            }
        }
        
        // Segunda prioridad: Usar el empresa_id del usuario autenticado
        if (Auth::check() && !empty(Auth::user()->empresa_id)) {
            $detalleNomina->empresa_id = Auth::user()->empresa_id;
        }
    }
}

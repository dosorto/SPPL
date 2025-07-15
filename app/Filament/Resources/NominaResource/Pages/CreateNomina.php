<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\DetalleNominas;
use App\Models\Empleado;

class CreateNomina extends CreateRecord
{
    protected static string $resource = NominaResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState(); // Estado del formulario

        foreach ($data['empleadosSeleccionados'] as $empleadoInput) {
            if (!empty($empleadoInput['seleccionado'])) {
                // ✅ Cargamos el empleado desde la base de datos, con sus deducciones
                $empleado = Empleado::with('deduccionesAplicadas.deduccion')->find($empleadoInput['empleado_id']);

                // 🧮 Sumamos el valor total de las deducciones aplicadas
                $totalDeducciones = $empleado->deduccionesAplicadas->sum(function ($relacion) {
                    return $relacion->deduccion->valor ?? 0;
                });

                // 💰 Calculamos el sueldo neto
                $sueldoNeto = $empleado->salario - $totalDeducciones;

                DetalleNominas::create([
                    'nomina_id' => $this->record->id,
                    'empleado_id' => $empleado->id,
                    'sueldo_bruto' => $empleado->salario,
                    'deducciones' => $totalDeducciones,
                    'sueldo_neto' => $sueldoNeto,
                    'total_horas_extra' => 0,
                    'horas_extra_monto' => 0,
                    'created_by' => auth()->id(),
                ]);
            }
        }
    }
}

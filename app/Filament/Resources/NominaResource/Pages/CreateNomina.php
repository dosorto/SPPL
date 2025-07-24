<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\DetalleNominas;
use App\Models\Empleado;

class CreateNomina extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected static string $resource = NominaResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $nomina = \App\Models\Nominas::create([
            'empresa_id' => $data['empresa_id'],
            'mes' => $data['mes'],
            'año' => $data['año'],
            'descripcion' => $data['descripcion'],
            'estado' => 'pendiente',
            'created_by' => auth()->id(),
        ]);

        foreach ($data['empleadosSeleccionados'] as $empleadoInput) {
            if (!empty($empleadoInput['seleccionado'])) {
                $empleado = \App\Models\Empleado::find($empleadoInput['empleado_id']);
                $sueldo = $empleado->salario;
                $deducciones = $empleado->deduccionesAplicadas->sum(function ($relacion) use ($sueldo) {
                    $deduccion = $relacion->deduccion;
                    if (!$deduccion) return 0;
                    if (trim(strtolower($deduccion->tipo_valor)) === 'porcentaje') {
                        return ($sueldo * ($deduccion->valor / 100));
                    }
                    return $deduccion->valor;
                });
                $deduccionesDetalle = $empleado->deduccionesAplicadas->map(function ($relacion) use ($sueldo) {
                    $deduccion = $relacion->deduccion;
                    if (!$deduccion) return null;
                    $nombre = $deduccion->deduccion ?? '';
                    $tipo = trim(strtolower($deduccion->tipo_valor)) === 'porcentaje' ? 'Porcentaje' : 'Monto';
                    $valor = $tipo === 'Porcentaje' ? ($deduccion->valor . '%') : $deduccion->valor;
                    return $nombre . ': ' . $valor;
                })->filter()->values()->implode("\n");
                $percepciones = $empleado->percepcionesAplicadas->sum(function ($relacion) {
                    $percepcion = $relacion->percepcion;
                    if (!$percepcion) return 0;
                    if (($percepcion->percepcion ?? '') === 'Horas Extras') {
                        $cantidad = $relacion->cantidad_horas ?? 0;
                        $valorUnitario = $percepcion->valor ?? 0;
                        return $cantidad * $valorUnitario;
                    }
                    return $percepcion->valor ?? 0;
                });
                $percepcionesDetalle = $empleado->percepcionesAplicadas->map(function ($relacion) {
                    $percepcion = $relacion->percepcion;
                    if (!$percepcion) return null;
                    $nombre = $percepcion->percepcion ?? '';
                    $valor = $percepcion->valor;
                    return $nombre . ': ' . $valor;
                })->filter()->values()->implode("\n");
                $total = $sueldo + $percepciones - $deducciones;
                \App\Models\DetalleNominas::create([
                    'nomina_id' => $nomina->id,
                    'empleado_id' => $empleadoInput['empleado_id'],
                    'empresa_id' => $nomina->empresa_id,
                    'sueldo_bruto' => $sueldo,
                    'deducciones' => $deducciones,
                    'deducciones_detalle' => $deduccionesDetalle,
                    'percepciones' => $percepciones,
                    'percepciones_detalle' => $percepcionesDetalle,
                    'sueldo_neto' => $total,
                    'created_by' => auth()->id(),
                ]);
            }
        }
        return $nomina;
    }
}

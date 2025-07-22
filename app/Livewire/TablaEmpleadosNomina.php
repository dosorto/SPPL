<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DetalleNominas;
use App\Models\Nominas;
use App\Models\Empleado;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class TablaEmpleadosNomina extends Component
{
    public $nominaId;
    public $empleados = [];
    
    public function mount($nominaId)
    {
        $this->nominaId = $nominaId;
        $this->cargarEmpleados();
    }
    
    #[On('agregarEmpleados')]
    
    public function cargarEmpleados()
    {
        $nomina = Nominas::find($this->nominaId);
        if ($nomina) {
            $this->empleados = $nomina->detalleNominas()->with('empleado.persona')->get();
        }
    }
    
    public function eliminarEmpleado($detalleId)
    {
        $detalle = DetalleNominas::find($detalleId);
        
        if ($detalle && $detalle->nomina_id == $this->nominaId) {
            $detalle->delete();
            
            Notification::make()
                ->title('Registro eliminado')
                ->body('Registro de pago eliminado correctamente del historial.')
                ->success()
                ->send();
                
            $this->cargarEmpleados();
        } else {
            Notification::make()
                ->title('Error')
                ->body('No se pudo eliminar el empleado de la nómina.')
                ->danger()
                ->send();
        }
    }

    public function agregarEmpleados($empleadosIds)
    {
        if (empty($empleadosIds)) {
            return;
        }
        
        $nomina = Nominas::find($this->nominaId);
        if (!$nomina) return;
        
        foreach ($empleadosIds as $empleadoId) {
            $empleado = Empleado::find($empleadoId);
            
            if (!$empleado) {
                continue;
            }
            
            $sueldo = $empleado->salario;
            
            $deducciones = $empleado->deduccionesAplicadas->sum(function ($relacion) use ($sueldo) {
                $deduccion = $relacion->deduccion;
                if (!$deduccion) return 0;
                if (trim(strtolower($deduccion->tipo_valor)) === 'porcentaje') {
                    return ($sueldo * ($deduccion->valor / 100));
                }
                return $deduccion->valor;
            });
            
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
            
            $total = $sueldo + $percepciones - $deducciones;
            
            DetalleNominas::create([
                'nomina_id' => $nomina->id,
                'empleado_id' => $empleadoId,
                'sueldo_bruto' => $sueldo,
                'deducciones' => $deducciones,
                'percepciones' => $percepciones,
                'sueldo_neto' => $total,
                'created_by' => auth()->id(),
            ]);
        }
        
            Notification::make()
                ->title('Registros de pago agregados')
                ->body('Registros de pago agregados correctamente al historial.')
                ->success()
                ->send();        $this->cargarEmpleados();
    }
    
    public function render()
    {
        return view('livewire.tabla-empleados-nomina');
    }
}

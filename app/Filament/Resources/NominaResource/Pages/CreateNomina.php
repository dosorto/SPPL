<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\DetalleNominas;
use App\Models\Empleado;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;

class CreateNomina extends CreateRecord
{
    // ...existing code...
    public function toggleSeleccionTodos(): void
    {
        $todosSeleccionados = collect($this->empleadosSeleccionados)->every(fn($e) => !empty($e['seleccionado']));
        foreach ($this->empleadosSeleccionados as $i => $empleado) {
            $this->empleadosSeleccionados[$i]['seleccionado'] = !$todosSeleccionados;
        }
    }

    public function deseleccionarTodos(): void
    {
        foreach ($this->empleadosSeleccionados as $i => $empleado) {
            $this->empleadosSeleccionados[$i]['seleccionado'] = false;
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected static string $resource = NominaResource::class;
    
    // Propiedades para la vista Blade personalizada
    public ?array $empleadosSeleccionados = [];
    public ?string $descripcion = null;
    public ?int $mes = null;
    public $año;
    public ?string $empresaNombre = null;
    public ?string $mesNombre = null;
    public $mostrarErrorMes = false;
    
    protected static string $view = 'filament.resources.nominas-resource.pages.create-nomina';
    
    public function mount(): void
    {
        parent::mount();
        $this->año = date('Y');
        
        // Cargar nombre de empresa
        $empresa = Empresa::find(Filament::auth()->user()?->empresa_id);
        $this->empresaNombre = $empresa ? $empresa->nombre : '';
        
        // Cargar empleados con sus deducciones y percepciones
        $this->cargarEmpleados();
    }
    
    // Método para cargar los datos de los empleados
    protected function cargarEmpleados(): void
    {
        $this->empleadosSeleccionados = \App\Models\Empleado::with(['deduccionesAplicadas.deduccion', 'percepcionesAplicadas.percepcion'])->get()->map(function ($empleado) {
            $salario = $empleado->salario;
            $deduccionesArray = $empleado->deduccionesAplicadas->map(function ($relacion) use ($salario) {
                $deduccion = $relacion->deduccion;
                if (!$deduccion) return null;
                $tipo = trim(strtolower($deduccion->tipo_valor));
                $valorCalculado = $tipo === 'porcentaje' ? ($salario * ($deduccion->valor / 100)) : $deduccion->valor;
                return [
                    'id' => $deduccion->id,
                    'nombre' => $deduccion->deduccion ?? '',
                    'tipo' => $tipo,
                    'valor' => $deduccion->valor,
                    'aplicada' => true,
                    'valorMostrado' => $tipo === 'porcentaje' ? rtrim(rtrim($deduccion->valor, '0'), '.') . '%' : 'L' . number_format($deduccion->valor, 2),
                    'valorCalculado' => $valorCalculado,
                ];
            })->filter()->values()->toArray();
            $percepcionesArray = $empleado->percepcionesAplicadas->map(function ($relacion) {
                $percepcion = $relacion->percepcion;
                if (!$percepcion) return null;
                $nombre = $percepcion->percepcion ?? '';
                if ($nombre === 'Horas Extras') {
                    $cantidad = $relacion->cantidad_horas ?? 0;
                    $valorUnitario = $percepcion->valor ?? 0;
                    $monto = $cantidad * $valorUnitario;
                    return [
                        'nombre' => $nombre . ' (' . $cantidad . 'h)',
                        'valorMostrado' => $monto,
                        'valorCalculado' => $monto,
                        'aplicada' => true,
                    ];
                }
                $tipo = trim(strtolower($percepcion->tipo_valor ?? '')) === 'porcentaje' ? 'Porcentaje' : 'Monto';
                $valor = $tipo === 'Porcentaje' ? ($percepcion->valor . '%') : $percepcion->valor;
                return [
                    'nombre' => $nombre,
                    'valorMostrado' => $valor,
                    'valorCalculado' => $percepcion->valor ?? 0,
                    'aplicada' => true,
                ];
            })->filter()->values()->toArray();
            $percepcionesTexto = collect($percepcionesArray)->map(function ($item) {
                return $item['nombre'] . ': ' . $item['valorMostrado'];
            })->implode("\n");
            $totalDeducciones = collect($deduccionesArray)->sum(function ($item) { return $item['aplicada'] ? $item['valorCalculado'] : 0; });
            $totalPercepciones = collect($percepcionesArray)->sum(function ($item) { return $item['aplicada'] ? $item['valorCalculado'] : 0; });
            $total = $salario + $totalPercepciones - $totalDeducciones;
            return [
                'empleado_id' => $empleado->id,
                'nombre' => $empleado->persona->primer_nombre . ' ' . $empleado->persona->primer_apellido,
                'salario' => $salario,
                'deduccionesArray' => $deduccionesArray,
                'percepcionesArray' => $percepcionesArray,
                'percepciones' => $percepcionesTexto,
                'total' => $total,
                'seleccionado' => true,
            ];
        })->toArray();
    }
    
    // Método para actualizar totales cuando se cambian deducciones
    public function updatedEmpleadosSeleccionados($value, $key): void
    {
        // Si es un cambio en una deducción o percepción
        if ((strpos($key, 'deduccionesArray') !== false && strpos($key, 'aplicada') !== false) ||
            (strpos($key, 'percepcionesArray') !== false && strpos($key, 'aplicada') !== false)) {
            $parts = explode('.', $key);
            $empleadoIndex = $parts[0];
            // Recalcular total de deducciones
            $deducciones = $this->empleadosSeleccionados[$empleadoIndex]['deduccionesArray'];
            $totalDeducciones = 0;
            foreach ($deducciones as $deduccion) {
                if (isset($deduccion['aplicada']) && $deduccion['aplicada']) {
                    $totalDeducciones += $deduccion['valorCalculado'] ?? 0;
                }
            }
            // Recalcular total de percepciones
            $percepciones = $this->empleadosSeleccionados[$empleadoIndex]['percepcionesArray'];
            $totalPercepciones = 0;
            foreach ($percepciones as $percepcion) {
                if (isset($percepcion['aplicada']) && $percepcion['aplicada']) {
                    $totalPercepciones += $percepcion['valorCalculado'] ?? 0;
                }
            }
            // Actualizar el total
            $salario = $this->empleadosSeleccionados[$empleadoIndex]['salario'];
            $this->empleadosSeleccionados[$empleadoIndex]['total'] = $salario + $totalPercepciones - $totalDeducciones;
        }
    }
    
    // Actualizar el nombre del mes cuando cambia
    public function updatedMes($value): void
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $this->mesNombre = $meses[$value] ?? '';
    }

    public function create(bool $another = false): void
    {
        // Validar que el mes esté seleccionado
        if (empty($this->mes)) {
            $this->mostrarErrorMes = true;
            return;
        } else {
            $this->mostrarErrorMes = false;
        }

        // Validar que al menos un empleado esté seleccionado
        $haySeleccionado = collect($this->empleadosSeleccionados)->contains(function ($empleado) {
            return isset($empleado['seleccionado']) && $empleado['seleccionado'];
        });

        if (!$haySeleccionado) {
            $this->addError('empleadosSeleccionados', 'Debe seleccionar al menos un empleado para crear la nómina.');
            return;
        }

        // Crear la nómina
        $nomina = \App\Models\Nominas::create([
            'empresa_id' => Filament::auth()->user()?->empresa_id,
            'mes' => $this->mes,
            'año' => $this->año,
            'descripcion' => $this->descripcion,
            'estado' => 'pendiente',
            'created_by' => auth()->id(),
        ]);

        // Crear los detalles de nómina para cada empleado seleccionado
        foreach ($this->empleadosSeleccionados as $empleadoInput) {
            if (!empty($empleadoInput['seleccionado'])) {
                $empleadoId = $empleadoInput['empleado_id'];
                $empleado = \App\Models\Empleado::find($empleadoId);

                if (!$empleado) continue;

                $sueldo = $empleado->salario;

                // Calcular deducciones
                $deducciones = 0;
                $deduccionesDetalle = "";

                if (isset($empleadoInput['deduccionesArray'])) {
                    foreach ($empleadoInput['deduccionesArray'] as $deduccion) {
                        if (isset($deduccion['aplicada']) && $deduccion['aplicada']) {
                            $deducciones += $deduccion['valorCalculado'] ?? 0;
                            $deduccionesDetalle .= $deduccion['nombre'] . ': ' . $deduccion['valorMostrado'] . "\n";
                        }
                    }
                }

                // Calcular percepciones
                $percepciones = 0;
                $percepcionesDetalle = "";

                if (isset($empleadoInput['percepcionesArray'])) {
                    foreach ($empleadoInput['percepcionesArray'] as $percepcion) {
                        $percepciones += $percepcion['valorCalculado'] ?? 0;
                        $percepcionesDetalle .= $percepcion['nombre'] . ': ' . $percepcion['valorMostrado'] . "\n";
                    }
                }

                // Calcular total
                $total = $sueldo + $percepciones - $deducciones;

                // Crear detalle
                \App\Models\DetalleNominas::create([
                    'nomina_id' => $nomina->id,
                    'empleado_id' => $empleadoId,
                    'empresa_id' => $nomina->empresa_id,
                    'sueldo_bruto' => $sueldo,
                    'deducciones' => $deducciones,
                    'deducciones_detalle' => trim($deduccionesDetalle),
                    'percepciones' => $percepciones,
                    'percepciones_detalle' => trim($percepcionesDetalle),
                    'sueldo_neto' => $total,
                    'created_by' => auth()->id(),
                ]);
            }
        }

        $this->redirect($this->getRedirectUrl());
    }
    
    // Sobrescribimos este método para integrarlo con nuestra lógica personalizada
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Este método se mantiene para compatibilidad con Filament,
        // pero la creación real ocurre en el método create()
        return new \App\Models\Nominas();
    }
}

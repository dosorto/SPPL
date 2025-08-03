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
// Propiedades para el modal
public $modalEditarPercepcionAbierto = false;
public $modalPercepcionValor = '';
public $modalPercepcionEmpleadoIndex = null;
public $modalPercepcionIndex = null;

// Modal para edición de cantidad
public $modalEditarCantidadAbierto = false;
public $modalCantidadValor = '';
public $modalCantidadEmpleadoIndex = null;
public $modalCantidadPercepcionIndex = null;
public $modalCantidadUnidad = 'horas';



// Ya no es necesario el modal para edición de tipo de pago por empleado
// Se define a nivel de nómina completa

// Abrir el modal y cargar el valor actual
public function abrirModalEditarPercepcion($empleadoIndex, $percepcionIndex)
{
    $this->modalPercepcionEmpleadoIndex = $empleadoIndex;
    $this->modalPercepcionIndex = $percepcionIndex;
    $this->modalPercepcionValor = $this->empleadosSeleccionados[$empleadoIndex]['percepcionesArray'][$percepcionIndex]['valorMostrado'] ?? '';
    $this->modalEditarPercepcionAbierto = true;
}

// Cerrar el modal
public function cerrarModalEditarPercepcion()
{
    $this->modalEditarPercepcionAbierto = false;
    $this->modalPercepcionEmpleadoIndex = null;
    $this->modalPercepcionIndex = null;
    $this->modalPercepcionValor = '';
}

// Abrir el modal de cantidad y cargar el valor actual
public function abrirModalEditarCantidad($empleadoIndex, $percepcionIndex)
{
    $this->modalCantidadEmpleadoIndex = $empleadoIndex;
    $this->modalCantidadPercepcionIndex = $percepcionIndex;
    $this->modalCantidadValor = $this->empleadosSeleccionados[$empleadoIndex]['percepcionesArray'][$percepcionIndex]['cantidad'] ?? '';
    $this->modalCantidadUnidad = $this->empleadosSeleccionados[$empleadoIndex]['percepcionesArray'][$percepcionIndex]['unidad'] ?? 'horas';
    $this->modalEditarCantidadAbierto = true;
}

// Cerrar el modal de cantidad
public function cerrarModalEditarCantidad()
{
    $this->modalEditarCantidadAbierto = false;
    $this->modalCantidadEmpleadoIndex = null;
    $this->modalCantidadPercepcionIndex = null;
    $this->modalCantidadValor = '';
}

// Ya no es necesario el modal de tipo de pago ya que se maneja a nivel de nómina



// Guardar el nuevo valor en el array y recalcular totales
public function guardarModalEditarPercepcion()
{
    $i = $this->modalPercepcionEmpleadoIndex;
    $p = $this->modalPercepcionIndex;
    $nuevoValor = trim($this->modalPercepcionValor);

    // Actualiza valorMostrado y valorCalculado
    $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['valorMostrado'] = $nuevoValor;

    // Si termina en %, es porcentaje, si no, es monto
    if (str_ends_with($nuevoValor, '%')) {
        $porcentaje = floatval(str_replace('%', '', $nuevoValor));
        $salario = $this->empleadosSeleccionados[$i]['salario'];
        $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['valorCalculado'] = ($porcentaje / 100) * $salario;
    } else {
        $monto = floatval(str_replace(['L', 'l', ' ', ','], ['', '', '', ''], $nuevoValor));
        $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['valorCalculado'] = $monto;
    }

    $this->recalcularTotalEmpleado($i);
    $this->cerrarModalEditarPercepcion();
}

// Guardar la nueva cantidad en el array y recalcular totales
public function guardarModalEditarCantidad()
{
    $i = $this->modalCantidadEmpleadoIndex;
    $p = $this->modalCantidadPercepcionIndex;
    $nuevaCantidad = floatval($this->modalCantidadValor);

    // Actualiza la cantidad
    $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['cantidad'] = $nuevaCantidad;
    
    // Si es una percepción tipo Horas Extras, recalculamos el valorCalculado
    $percepcion = $this->empleadosSeleccionados[$i]['percepcionesArray'][$p];
    if ($percepcion['depende_cantidad'] ?? false) {
        // Actualizamos el nombre para mostrar la cantidad
        $nombreBase = preg_replace('/\s*\(\d+.*?\)$/', '', $percepcion['nombre']);
        $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['nombre'] = $nombreBase . ' (' . $nuevaCantidad . ' ' . ($percepcion['unidad'] ?? 'hrs') . ')';
        
        // Recalculamos el valor en base a la cantidad
        $valorUnitario = $percepcion['valor_unitario'] ?? $percepcion['valorCalculado'] / max(1, ($percepcion['cantidad_previa'] ?? 1));
        $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['valor_unitario'] = $valorUnitario;
        $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['cantidad_previa'] = $nuevaCantidad;
        $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['valorCalculado'] = $valorUnitario * $nuevaCantidad;
        
        // Actualiza el valorMostrado para reflejar el cambio
        $this->empleadosSeleccionados[$i]['percepcionesArray'][$p]['valorMostrado'] = 'L. ' . number_format($valorUnitario * $nuevaCantidad, 2);
    }

    $this->recalcularTotalEmpleado($i);
    $this->cerrarModalEditarCantidad();
}

// Método para recalcular el total del empleado
private function recalcularTotalEmpleado($empleadoIndex)
{
    $salario = $this->empleadosSeleccionados[$empleadoIndex]['salario'];
    
    // Recalcular deducciones que son un porcentaje del salario
    if (!empty($this->empleadosSeleccionados[$empleadoIndex]['deduccionesArray'])) {
        foreach ($this->empleadosSeleccionados[$empleadoIndex]['deduccionesArray'] as $i => $deduccion) {
            if ($deduccion['tipo'] === 'porcentaje') {
                $this->empleadosSeleccionados[$empleadoIndex]['deduccionesArray'][$i]['valorCalculado'] = 
                    $salario * ($deduccion['valor'] / 100);
            }
        }
    }
    
    // Recalcular percepciones que son un porcentaje del salario
    if (!empty($this->empleadosSeleccionados[$empleadoIndex]['percepcionesArray'])) {
        foreach ($this->empleadosSeleccionados[$empleadoIndex]['percepcionesArray'] as $i => $percepcion) {
            if (isset($percepcion['valorMostrado']) && str_ends_with($percepcion['valorMostrado'], '%')) {
                $porcentaje = floatval(str_replace('%', '', $percepcion['valorMostrado']));
                $this->empleadosSeleccionados[$empleadoIndex]['percepcionesArray'][$i]['valorCalculado'] = 
                    ($porcentaje / 100) * $salario;
            }
        }
    }
    
    // Recalcula el total del empleado
    $totalDeducciones = collect($this->empleadosSeleccionados[$empleadoIndex]['deduccionesArray'] ?? [])->sum(function($item) {
        return ($item['aplicada'] ?? false) ? ($item['valorCalculado'] ?? 0) : 0;
    });
    
    $totalPercepciones = collect($this->empleadosSeleccionados[$empleadoIndex]['percepcionesArray'] ?? [])->sum(function($item) {
        return ($item['aplicada'] ?? false) ? ($item['valorCalculado'] ?? 0) : 0;
    });
    
    $this->empleadosSeleccionados[$empleadoIndex]['total'] = $salario + $totalPercepciones - $totalDeducciones;
}
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
    public ?string $tipo_pago = 'mensual';
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
        
        // Aplicar el tipo de pago por defecto
        if ($this->tipo_pago !== 'mensual') {
            $this->updatedTipoPago($this->tipo_pago);
        }
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
            $percepcionesArray = $empleado->percepcionesAplicadas->map(function ($relacion) use ($empleado) {
                $percepcion = $relacion->percepcion;
                if (!$percepcion) return null;
                $nombre = $percepcion->percepcion ?? '';
                
                // Para percepciones que dependen de una cantidad
                if ($percepcion->depende_cantidad) {
                    $cantidad = 0;
                    $valorUnitario = $percepcion->valor ?? 0;
                    $monto = 0;
                    $unidad = $percepcion->unidad_cantidad ?? 'hrs';
                    $tipo = trim(strtolower($percepcion->tipo_valor ?? ''));
                    $valorMostrado = $tipo === 'porcentaje'
                        ? rtrim(rtrim($valorUnitario, '0'), '.') . '% por ' . $unidad
                        : 'L. ' . number_format($valorUnitario, 2) ;
                    return [
                        'id' => $percepcion->id,
                        'nombre' => $nombre . ' (0 ' . $unidad . ')',
                        'valorMostrado' => $valorMostrado,
                        'valorCalculado' => $monto,
                        'aplicada' => true,
                        'depende_cantidad' => true,
                        'cantidad' => $cantidad,
                        'cantidad_previa' => $cantidad,
                        'valor_unitario' => $valorUnitario,
                        'unidad' => $unidad,
                    ];
                }
                // Para Horas Extras sin depende_cantidad (caso legacy)
                else if ($nombre === 'Horas Extras') {
                    $cantidad = $relacion->cantidad_horas ?? 0;
                    $valorUnitario = $percepcion->valor ?? 0;
                    $monto = $cantidad * $valorUnitario;
                    $unidad = 'hrs';
                    
                    return [
                        'id' => $percepcion->id,
                        'nombre' => $nombre . ' (' . $cantidad . ' ' . $unidad . ')',
                        'valorMostrado' => 'L. ' . number_format($monto, 2),
                        'valorCalculado' => $monto,
                        'aplicada' => true,
                        'depende_cantidad' => true,
                        'cantidad' => $cantidad,
                        'cantidad_previa' => $cantidad,
                        'valor_unitario' => $valorUnitario,
                        'unidad' => $unidad,
                    ];
                }
                
                // Para percepciones normales
                $tipo = trim(strtolower($percepcion->tipo_valor ?? '')) === 'porcentaje' ? 'Porcentaje' : 'Monto';
                $valor = $tipo === 'Porcentaje' ? ($percepcion->valor . '%') : 'L. ' . number_format($percepcion->valor, 2);
                $valorCalculado = $tipo === 'Porcentaje' ? 
                    ($empleado->salario * ($percepcion->valor / 100)) : 
                    $percepcion->valor;
                
                return [
                    'id' => $percepcion->id,
                    'nombre' => $nombre,
                    'valorMostrado' => $valor,
                    'valorCalculado' => $valorCalculado,
                    'aplicada' => true,
                    'depende_cantidad' => false,
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
                'salario_base' => $salario, // Guardamos el salario base para cálculos
                'deduccionesArray' => $deduccionesArray,
                'percepcionesArray' => $percepcionesArray,
                'percepciones' => $percepcionesTexto,
                'total' => $total,
                // Ya no necesitamos tipo_pago por empleado, se define a nivel de nómina
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
    
    // Actualizar salarios cuando cambia el tipo de pago
    public function updatedTipoPago($value): void
    {
        foreach ($this->empleadosSeleccionados as $index => $empleado) {
            $salarioBase = $empleado['salario_base'] ?? $empleado['salario'];
            
            // Ajustar el salario según el tipo de pago
            switch ($value) {
                case 'quincenal':
                    $this->empleadosSeleccionados[$index]['salario'] = $salarioBase / 2;
                    break;
                case 'semanal':
                    $this->empleadosSeleccionados[$index]['salario'] = $salarioBase / 4.33; // Aproximadamente 52/12 semanas por mes
                    break;
                default: // mensual
                    $this->empleadosSeleccionados[$index]['salario'] = $salarioBase;
                    break;
            }
            
            // Recalcular totales para reflejar los cambios en el salario
            $this->recalcularTotalEmpleado($index);
        }
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
            'tipo_pago' => $this->tipo_pago,
            'estado' => 'pendiente',
            'created_by' => auth()->id(),
        ]);

        // Crear los detalles de nómina para cada empleado seleccionado
        foreach ($this->empleadosSeleccionados as $empleadoInput) {
            if (!empty($empleadoInput['seleccionado'])) {
                $empleadoId = $empleadoInput['empleado_id'];
                $empleado = \App\Models\Empleado::find($empleadoId);

                if (!$empleado) continue;

                // Obtener el salario base
                $salarioBase = $empleado->salario;
                
                // Ajustar el sueldo según el tipo de pago
                $sueldo = $salarioBase;
                switch ($this->tipo_pago) {
                    case 'quincenal':
                        $sueldo = $salarioBase / 2;
                        break;
                    case 'semanal':
                        $sueldo = $salarioBase / 4.33; // Aproximadamente 52/12 semanas por mes
                        break;
                    // Para mensual no hacemos ajustes, es el salario completo
                }

                // Calcular deducciones
                $deducciones = 0;
                $deduccionesDetalle = "";

                if (isset($empleadoInput['deduccionesArray'])) {
                    foreach ($empleadoInput['deduccionesArray'] as $deduccion) {
                        if (isset($deduccion['aplicada']) && $deduccion['aplicada']) {
                            // Recalcular deducciones porcentuales basadas en el sueldo ajustado
                            $valorCalculado = $deduccion['valorCalculado'] ?? 0;
                            if ($deduccion['tipo'] === 'porcentaje') {
                                $valorCalculado = $sueldo * ($deduccion['valor'] / 100);
                            }
                            
                            $deducciones += $valorCalculado;
                            $deduccionesDetalle .= $deduccion['nombre'] . ': ' . $deduccion['valorMostrado'] . "\n";
                        }
                    }
                }

                // Calcular percepciones
                $percepciones = 0;
                $percepcionesDetalle = "";

                if (isset($empleadoInput['percepcionesArray'])) {
                    foreach ($empleadoInput['percepcionesArray'] as $percepcion) {
                        if (isset($percepcion['aplicada']) && $percepcion['aplicada']) {
                            // Recalcular percepciones porcentuales basadas en el sueldo ajustado
                            $valorCalculado = $percepcion['valorCalculado'] ?? 0;
                            if (isset($percepcion['valorMostrado']) && str_ends_with($percepcion['valorMostrado'], '%')) {
                                $porcentaje = floatval(str_replace('%', '', $percepcion['valorMostrado']));
                                $valorCalculado = ($porcentaje / 100) * $sueldo;
                            }
                            
                            $percepciones += $valorCalculado;
                            $percepcionesDetalle .= $percepcion['nombre'] . ': ' . $percepcion['valorMostrado'];
                            $percepcionesDetalle .= "\n";
                        }
                    }
                }

                // Calcular total
                $total = $sueldo + $percepciones - $deducciones;
                
                // Usar el tipo de pago definido a nivel de nómina
                $tipoPago = $this->tipo_pago;

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
                    'tipo_pago' => $tipoPago, 
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

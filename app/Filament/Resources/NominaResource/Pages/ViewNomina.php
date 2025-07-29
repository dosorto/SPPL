<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\View;
use Filament\Pages\Actions\EditAction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ViewNomina extends ViewRecord
{
    protected static string $resource = NominaResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->columns(2);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Datos generales de la nómina')
                ->icon('heroicon-o-clipboard-document')
                ->schema([
                    Placeholder::make('empresa')
                        ->label('Empresa')
                        ->content(fn () => $this->record->empresa?->nombre ?? 'N/A'),
                    Placeholder::make('mes')
                        ->label('Mes')
                        ->content(function () {
                            $meses = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                                7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                            ];
                            return $meses[(int)($this->record->mes)] ?? $this->record->mes;
                        }),
                    Placeholder::make('año')
                        ->label('Año')
                        ->content(fn () => $this->record->año ?? 'N/A'),
                    Placeholder::make('descripcion')
                        ->label('Descripción')
                        ->content(fn () => $this->record->descripcion ?? 'N/A'),
                    Placeholder::make('cerrada')
                        ->label('Estado')
                        ->content(fn () => $this->record->cerrada ? 'Cerrada' : 'Abierta'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Historial de Pagos de Empleados')
                ->icon('heroicon-o-user-group')
                ->schema([
                    View::make('filament.nomina.tabla-empleados')
                        ->viewData([
                            'empleados' => $this->record->detalleNominas,
                        ])
                        ->columnSpan('full'),
                ])
                ->collapsible(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('imprimirNomina')
                ->label('Generar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return $this->generarPDF();
                }),

            EditAction::make()
                ->visible(fn () => !$this->record->cerrada),
                
            \Filament\Actions\Action::make('cerrarNomina')
                ->label('Cerrar Nómina')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(fn () => !$this->record->cerrada)
                ->requiresConfirmation()
                ->modalHeading('Cerrar Nómina')
                ->modalDescription('Una vez cerrada la nómina, no podrás editarla ni eliminarla. ¿Estás seguro de que deseas cerrarla?')
                ->modalSubmitActionLabel('Sí, cerrar nómina')
                ->action(function () {
                    $this->record->update([
                        'cerrada' => true
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Nómina cerrada')
                        ->body('La nómina y su historial de pagos han sido cerrados exitosamente.')
                        ->success()
                        ->send();
                        
                    $this->redirect(route('filament.admin.resources.nominas.view', $this->record));
                }),
        ];
    }

    public function generarPDF()
    {
        // Cargar la nómina actual con todas sus relaciones necesarias
        $nomina = $this->record->load([
            'empresa', 
            'detalleNominas.empleado.persona', 
            'detalleNominas.empleado.departamento',
            'detalleNominas.empleadoDeducciones.deduccion',
            'detalleNominas.empleadoPercepciones.percepcion'
        ]);
        
        if (!$nomina) {
            return response('No se encontró la nómina solicitada.', 404);
        }
        
        // Obtener el nombre del mes
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
        $mesNombre = $meses[$nomina->mes] ?? '';
        
        // Preparar los datos de los empleados para el PDF, usando los detalles guardados (solo los activos)
        $empleados = [];
        $totalNomina = 0;

        foreach ($nomina->detalleNominas as $detalle) {
            $empleado = $detalle->empleado;
            $persona = $empleado->persona ?? null;
            $departamento = $empleado->departamento ?? null;

            // Parsear deducciones activas desde deducciones_detalle
            $deducciones = [];
            $deduccionesDetalle = $detalle->deducciones_detalle ?? '';
            $deduccionesLineas = array_filter(array_map('trim', explode("\n", $deduccionesDetalle)));
            $sueldoBruto = $detalle->sueldo_bruto;
            if (empty($deduccionesLineas)) {
                $deducciones[] = [
                    'nombre' => 'DEBUG',
                    'valorMostrado' => 'deducciones_detalle vacío: ' . ($deduccionesDetalle === '' ? '[VACÍO]' : $deduccionesDetalle),
                    'aplicada' => false,
                    'valorNumerico' => 0,
                    'valorCalculado' => 0
                ];
            } else {
                foreach ($deduccionesLineas as $linea) {
                    if (strpos($linea, ':') !== false) {
                        [$nombre, $valorMostrado] = array_map('trim', explode(':', $linea, 2));
                        $valorNumerico = 0;
                        $valorCalculado = 0;
                        if (preg_match('/([\d\.,]+)/', $valorMostrado, $matches)) {
                            $valorNumerico = floatval(str_replace([',', 'L.', ' '], ['', '', ''], $matches[1]));
                        }
                        // Detectar porcentaje: termina en % (opcional espacios)
                        if (preg_match('/([\d\.,]+)\s*%\s*$/', $valorMostrado, $matchesPorc)) {
                            $porcentaje = floatval(str_replace([',', ' '], ['', ''], $matchesPorc[1]));
                            $valorCalculado = ($porcentaje / 100) * $sueldoBruto;
                        } else {
                            $valorCalculado = $valorNumerico;
                        }
                        $deducciones[] = [
                            'nombre' => $nombre,
                            'valorMostrado' => $valorMostrado,
                            'aplicada' => true,
                            'valorNumerico' => $valorNumerico,
                            'valorCalculado' => $valorCalculado
                        ];
                    }
                }
            }

            // Parsear percepciones activas desde percepciones_detalle
            $percepciones = [];
            $percepcionesDetalle = $detalle->percepciones_detalle ?? '';
            $percepcionesLineas = array_filter(array_map('trim', explode("\n", $percepcionesDetalle)));
            if (empty($percepcionesLineas)) {
                $percepciones[] = [
                    'nombre' => 'DEBUG',
                    'valorMostrado' => 'percepciones_detalle vacío: ' . ($percepcionesDetalle === '' ? '[VACÍO]' : $percepcionesDetalle),
                    'aplicada' => false,
                    'valorNumerico' => 0,
                    'valorCalculado' => 0
                ];
            } else {
                foreach ($percepcionesLineas as $linea) {
                    if (strpos($linea, ':') !== false) {
                        [$nombre, $valorMostrado] = array_map('trim', explode(':', $linea, 2));
                        $valorNumerico = 0;
                        $valorCalculado = 0;
                        if (preg_match('/([\d\.,]+)/', $valorMostrado, $matches)) {
                            $valorNumerico = floatval(str_replace([',', 'L.', ' '], ['', '', ''], $matches[1]));
                        }
                        if (preg_match('/([\d\.,]+)\s*%\s*$/', $valorMostrado, $matchesPorc)) {
                            $porcentaje = floatval(str_replace([',', ' '], ['', ''], $matchesPorc[1]));
                            $valorCalculado = ($porcentaje / 100) * $sueldoBruto;
                        } else {
                            $valorCalculado = $valorNumerico;
                        }
                        $percepciones[] = [
                            'nombre' => $nombre,
                            'valorMostrado' => $valorMostrado,
                            'aplicada' => true,
                            'valorNumerico' => $valorNumerico,
                            'valorCalculado' => $valorCalculado
                        ];
                    }
                }
            }

            // Nombre del empleado: primer_nombre, segundo_nombre, primer_apellido, segundo_apellido
            $nombreEmpleado = 'Empleado #' . $empleado->id;
            if ($persona) {
                $nombres = [];
                if (!empty($persona->primer_nombre)) $nombres[] = $persona->primer_nombre;
                if (!empty($persona->segundo_nombre)) $nombres[] = $persona->segundo_nombre;
                if (!empty($persona->primer_apellido)) $nombres[] = $persona->primer_apellido;
                if (!empty($persona->segundo_apellido)) $nombres[] = $persona->segundo_apellido;
                $nombreEmpleado = trim(implode(' ', $nombres));
                if ($nombreEmpleado === '') $nombreEmpleado = 'Empleado #' . $empleado->id;
            }

            // Calcular totales de deducciones y percepciones activas usando valorCalculado
            $totalDeducciones = collect($deducciones)->sum(function($item) {
                return (isset($item['aplicada']) && $item['aplicada']) ? ($item['valorCalculado'] ?? 0) : 0;
            });
            $totalPercepciones = collect($percepciones)->sum(function($item) {
                return (isset($item['aplicada']) && $item['aplicada']) ? ($item['valorCalculado'] ?? 0) : 0;
            });

            $empleados[] = [
                'id' => $empleado->id,
                'numero' => $empleado->numero_empleado,
                'nombre' => $nombreEmpleado,
                'departamento' => $departamento ? $departamento->nombre : '',
                'salario' => $detalle->sueldo_bruto,
                'deduccionesArray' => $deducciones,
                'deducciones' => $totalDeducciones,
                'percepcionesArray' => $percepciones,
                'percepciones' => $totalPercepciones,
                'total' => $detalle->sueldo_neto
            ];

            $totalNomina += $detalle->sueldo_neto;
        }
        
        // Generar el PDF
        $pdf = PDF::loadView('pdf.nomina', [
            'nomina' => $nomina,
            'empresa' => $nomina->empresa,
            'mesNombre' => $mesNombre,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'empleados' => $empleados,
            'totalNomina' => $totalNomina,
        ]);
        
        // Configurar opciones del PDF para mejor manejo de UTF-8
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
        
        // Usar streamDownload como en el módulo de órdenes para mejor manejo de memoria
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "nomina_{$mesNombre}_{$this->record->año}.pdf");
    }
}

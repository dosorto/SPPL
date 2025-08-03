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
                    Placeholder::make('tipo_pago')
                        ->label('Tipo de Pago')
                        ->content(function () {
                            $tipos = [
                                'mensual' => 'Mensual',
                                'quincenal' => 'Quincenal',
                                'semanal' => 'Semanal',
                            ];
                            return $tipos[$this->record->tipo_pago] ?? ucfirst($this->record->tipo_pago ?? 'mensual');
                        }),
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
            \Filament\Actions\Action::make('exportar_excel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => $this->generarExcel()),

            \Filament\Actions\Action::make('imprimirNomina')
                ->label('Generar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    return $this->generarPDF();
                }),

            EditAction::make()
                ->visible(fn () => !$this->record->cerrada),

            \Filament\Actions\Action::make('cerrarNomina')
                ->label('Cerrar Nómina')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
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

    /**
     * Obtiene los datos de la nómina y empleados para exportar (PDF/Excel)
     * @return array|null
     */
    private function getNominaExportData()
    {
        $nomina = $this->record->load([
            'empresa',
            'detalleNominas.empleado.persona',
            'detalleNominas.empleado.departamento',
            'detalleNominas.empleadoDeducciones.deduccion',
            'detalleNominas.empleadoPercepciones.percepcion',
        ]);
        if (!$nomina) {
            return null;
        }
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $mesNombre = $meses[$nomina->mes] ?? '';
        $tiposPago = [
            'mensual' => 'Mensual',
            'quincenal' => 'Quincenal',
            'semanal' => 'Semanal',
            'm' => 'Mensual',
            'q' => 'Quincenal',
            's' => 'Semanal',
        ];
        $tipoPagoNombre = $tiposPago[$nomina->tipo_pago] ?? ucfirst($nomina->tipo_pago ?? 'mensual');
        $empleados = [];
        $totalNomina = 0;
        foreach ($nomina->detalleNominas as $detalle) {
            $empleado = $detalle->empleado;
            $persona = $empleado->persona ?? null;
            $departamento = $empleado->departamento ?? null;
            $sueldoBruto = $detalle->sueldo_bruto ?? $empleado->salario;
            // Deducciones
            $deducciones = [];
            $deduccionesDetalle = $detalle->deducciones_detalle ?? '';
            $deduccionesLineas = array_filter(array_map('trim', explode("\n", $deduccionesDetalle)));
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
            // Percepciones
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
                        $valorLimpio = preg_replace('/\s*\(Cantidad:.*?\)/', '', $valorMostrado);
                        if (preg_match('/([\d\.,]+)/', $valorLimpio, $matches)) {
                            $valorNumerico = floatval(str_replace([',', 'L.', ' '], ['', '', ''], $matches[1]));
                        }
                        if (preg_match('/([\d\.,]+)\s*%/', $valorLimpio, $matchesPorc)) {
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
            // Nombre del empleado
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
            $totalDeducciones = collect($deducciones)->sum(function($item) {
                return (isset($item['aplicada']) && $item['aplicada']) ? ($item['valorCalculado'] ?? 0) : 0;
            });
            $empleados[] = [
                'id' => $empleado->id,
                'numero' => $empleado->numero_empleado,
                'nombre' => $nombreEmpleado,
                'departamento' => $departamento ? $departamento->nombre : '',
                'salario' => $sueldoBruto,
                'deduccionesArray' => $deducciones,
                'deducciones' => $totalDeducciones,
                'percepcionesArray' => $percepciones,
                'percepciones' => $detalle->percepciones, // Usar el valor guardado en la BD
                'total' => $detalle->sueldo_neto
            ];
            $totalNomina += $detalle->sueldo_neto;
        }
        return [
            'nomina' => $nomina,
            'mesNombre' => $mesNombre,
            'tipoPagoNombre' => $tipoPagoNombre,
            'empleados' => $empleados,
            'totalNomina' => $totalNomina,
        ];
    }

    public function generarPDF()
    {
        $data = $this->getNominaExportData();
        if (!$data) {
            return response('No se encontró la nómina solicitada.', 404);
        }
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.nomina', array_merge($data, [
            'empresa' => $data['nomina']->empresa,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
        ]));
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "nomina_{$data['mesNombre']}_{$this->record->año}.pdf");
    }

    public function generarExcel()
    {
        $data = $this->getNominaExportData();
        if (!$data) {
            return response('No se encontró la nómina solicitada.', 404);
        }
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\NominaExport(
                $data['nomina'],
                $data['mesNombre'],
                $data['tipoPagoNombre'],
                $data['empleados'],
                $data['totalNomina']
            ),
            "nomina_{$data['mesNombre']}_{$this->record->año}.xlsx"
        );
    }
}

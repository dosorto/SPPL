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
                        ->content(fn () => $this->record->mes ?? 'N/A'),
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
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('imprimirNomina')
                ->label('Imprimir Nómina')
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

    public function generarPDF(): Response
    {
        // Obtener la nómina actual
        $nomina = $this->record;
        
        if (!$nomina) {
            return response('No se encontró la nómina solicitada.', 404);
        }
        
        // Obtener los detalles de la nómina (empleados)
        $detallesNomina = $nomina->detalleNominas;
        
        // Preparar los datos para el PDF
        $empleadosData = [];
        $totalGeneral = 0;
        
        foreach ($detallesNomina as $detalle) {
            $empleado = $detalle->empleado;
            if (!$empleado) continue;
            
            // Convertir los detalles de deducciones y percepciones a arrays
            $deduccionesArray = [];
            $deduccionesDetalle = explode("\n", trim($detalle->deducciones_detalle ?? ''));
            foreach ($deduccionesDetalle as $deduccion) {
                if (!empty($deduccion)) {
                    $deduccionesArray[] = [
                        'nombre' => $deduccion,
                        'aplicada' => true,
                        'valorMostrado' => ''
                    ];
                }
            }
            
            $percepcionesArray = [];
            $percepcionesDetalle = explode("\n", trim($detalle->percepciones_detalle ?? ''));
            foreach ($percepcionesDetalle as $percepcion) {
                if (!empty($percepcion)) {
                    $percepcionesArray[] = [
                        'nombre' => $percepcion,
                        'aplicada' => true,
                        'valorMostrado' => ''
                    ];
                }
            }
            
            $empleadosData[] = [
                'nombre' => $empleado->getNombreCompletoAttribute(),
                'salario' => $detalle->sueldo_bruto,
                'deduccionesArray' => $deduccionesArray,
                'percepcionesArray' => $percepcionesArray,
                'total' => $detalle->sueldo_neto,
                'seleccionado' => true,
            ];
            
            $totalGeneral += $detalle->sueldo_neto;
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
        
        // Generar el PDF con configuración de codificación
        $pdf = PDF::loadView('pdf.nomina', [
            'empresa' => $nomina->empresa,
            'empleados' => $empleadosData,
            'mesNombre' => $mesNombre,
            'año' => $nomina->año,
            'descripcion' => $nomina->descripcion,
        ]);
        
        // Configurar opciones del PDF para UTF-8
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        
        // Descargar el PDF
        return $pdf->download('nomina_'.$mesNombre.'_'.$nomina->año.'.pdf');
    }
}

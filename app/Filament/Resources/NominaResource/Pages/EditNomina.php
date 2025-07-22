<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Section;
use App\Models\Empleado;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;

class EditNomina extends EditRecord
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (Model $record) => !$record->cerrada),
        ];
    }
    
    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();
        
        $record = $this->getRecord();
        
        if ($record->cerrada) {
            $this->redirect(route('filament.admin.resources.nominas.view', $this->record));
            
            \Filament\Notifications\Notification::make()
                ->title('Nómina cerrada')
                ->body('Esta nómina está cerrada y no puede ser editada.')
                ->warning()
                ->send();
        }
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Section::make('Información de la nómina')
                    ->schema([
                        \Filament\Forms\Components\Select::make('mes')
                            ->label('Mes')
                            ->options([
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
                            ])
                            ->required(),

                        \Filament\Forms\Components\TextInput::make('descripcion')
                            ->label('Descripción')
                            ->maxLength(255),

                        // Campo estado eliminado porque ya no se usará
                    ]),

                // Sección especial para empleados (después del formulario principal)
                Section::make('Empleados en la Nómina')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('tabla-empleados')
                            ->content(function ($record) {
                                return view('filament.components.livewire-wrapper', [
                                    'component' => 'tabla-empleados-nomina',
                                    'params' => ['nominaId' => $record->id]
                                ]);
                            }),
                    ])
                    ->extraAttributes([
                        'class' => 'empleados-nomina-section',
                    ]),
            ]);
    }

    public function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('addEmpleado')
                ->label('Agregar empleados a la nómina')
                ->modalHeading('Agregar empleados a la nómina')
                ->form([
                    \Filament\Forms\Components\CheckboxList::make('empleadosParaAgregar')
                        ->label('Empleados disponibles para agregar')
                        ->options(function () {
                            $empleadosEnNomina = $this->record->detalleNominas->pluck('empleado_id')->toArray();
                            return Empleado::whereNotIn('id', $empleadosEnNomina)->get()->mapWithKeys(function($empleado) {
                                return [$empleado->id => $empleado->persona->primer_nombre . ' ' . $empleado->persona?->primer_apellido];
                            })->toArray();
                        })
                        ->columns(2),
                ])
                ->action(function (array $data) {
                    $empleadosIds = $data['empleadosParaAgregar'] ?? [];
                    if (empty($empleadosIds)) {
                        return;
                    }
                    $nomina = $this->record;
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
                        \App\Models\DetalleNominas::create([
                            'nomina_id' => $nomina->id,
                            'empleado_id' => $empleadoId,
                            'empresa_id' => $nomina->empresa_id,  // Añadimos el campo empresa_id
                            'sueldo_bruto' => $sueldo,
                            'deducciones' => $deducciones,
                            'percepciones' => $percepciones,
                            'sueldo_neto' => $total,
                            'created_by' => auth()->id(),
                        ]);
                    }
                    \Filament\Notifications\Notification::make()
                        ->title('Empleados agregados')
                        ->body('Empleados agregados correctamente a la nómina.')
                        ->success()
                        ->send();
                    // Refrescar la página para que Livewire recargue la tabla
                    $this->redirect(request()->header('Referer') ?? route('filament.admin.resources.nominas.edit', $this->record));
                })
                ->modalSubmitActionLabel('Agregar empleados')
                ->modalWidth('lg'),
        ];
    }
}

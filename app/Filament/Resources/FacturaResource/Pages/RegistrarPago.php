<?php

namespace App\Filament\Resources\FacturaResource\Pages;

use App\Filament\Resources\FacturaResource;
use App\Models\Factura;
use App\Models\MetodoPago;
use App\Models\Pago;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set; // Importar Set para actualizar campos
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class RegistrarPago extends EditRecord
{
    protected static string $resource = FacturaResource::class;
    protected static string $view = 'filament.resources.factura-resource.pages.registrar-pago';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $factura = $this->getRecord();
        $pagado = $factura->pagos()->sum('monto');
        $restante = round(max(0, $factura->total - $pagado), 2);

        $this->form->fill([
            'pagos' => [[
                'metodo_pago_id' => null,
                'monto' => $restante, // Inicializamos el primer monto con el restante total
                'referencia' => '',
                'monto_recibido' => null,
                'cambio' => null,
            ]],
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('total_a_pagar')
                    ->label('Total a Pagar')
                    ->content(function (Get $get) {
                        /** @var Factura $factura */
                        $factura = $this->getRecord();
                        $pagadoEnDB = $factura->pagos()->sum('monto');
                        $pagosEnFormulario = $get('pagos') ?? [];

                        $totalRecibidoEnFormulario = 0;
                        foreach ($pagosEnFormulario as $pago) {
                            $totalRecibidoEnFormulario += (float)($pago['monto_recibido'] ?? 0);
                        }

                        $saldoPendiente = $factura->total - $pagadoEnDB - $totalRecibidoEnFormulario;
                        return 'L. ' . number_format(round(max(0, $saldoPendiente), 2), 2);
                    })
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'text-2xl font-bold text-primary-600 dark:text-primary-400',
                    ])
                    ->helperText('Este es el monto restante de la factura que debe ser cubierto.')
                    ->live(), // Aseguramos que este placeholder sea reactivo

                Forms\Components\Repeater::make('pagos')
                    ->label('Métodos de Pago')
                    ->schema([
                        Forms\Components\Select::make('metodo_pago_id')
                            ->label('Método de Pago')
                            ->options(MetodoPago::pluck('nombre', 'id'))
                            ->required()
                            ->columnSpan(2)
                            ->searchable()
                            ->live(),

                        Forms\Components\TextInput::make('monto')
                            ->label('Monto a Cubrir')
                            ->numeric()
                            ->required()
                            ->columnSpan(2)
                            ->disabled()
                            ->live() // Es crucial que este campo sea live
                            ->default(function (Get $get) {
                                /** @var Factura $factura */
                                $factura = $this->getRecord();
                                $pagadoEnDB = $factura->pagos()->sum('monto');
                                $pagosEnFormulario = $get('../../pagos') ?? [];
                                $currentIndex = (int) $get('__index');

                                $montoRecibidoEnPagosAnteriores = 0;
                                for ($i = 0; $i < $currentIndex; $i++) {
                                    $montoRecibidoEnPagosAnteriores += (float)($pagosEnFormulario[$i]['monto_recibido'] ?? 0);
                                }

                                $restante = $factura->total - $pagadoEnDB - $montoRecibidoEnPagosAnteriores;
                                return round(max(0, $restante), 2);
                            })
                            ->hidden(), // Oculta el campo 'monto' del usuario

                        Forms\Components\TextInput::make('referencia')
                            ->label('Referencia (opcional)')
                            ->columnSpan(2)
                            ->maxLength(50)
                            ->visible(function (Get $get) {
                                $metodoPago = MetodoPago::find($get('metodo_pago_id'));
                                $nombreMetodo = optional($metodoPago)->nombre;
                                return in_array($nombreMetodo, ['Tarjeta de Crédito', 'Tarjeta de Débito', 'Transferencia Bancaria']);
                            }),

                        Forms\Components\TextInput::make('monto_recibido')
                            ->label('Monto Recibido')
                            ->numeric()
                            ->reactive()
                            ->live(onBlur: true) // Cambiado de live() a live(onBlur: true) para mayor flexibilidad al escribir
                            ->afterStateUpdated(function ($state, callable $get, callable $set, $livewire, $component) {
                                // 1. Calcular el cambio para el ítem actual
                                $montoACubrir = (float) $get('monto') ?? 0;
                                $recibido = (float) $state ?? 0;
                                $cambio = $recibido - $montoACubrir;
                                $set('cambio', round($cambio, 2));

                                // 2. Recalcular y actualizar 'monto' y 'cambio' para TODOS los ítems del repetidor
                                // Esto asegura que los montos a cubrir y cambios subsiguientes se actualicen
                                /** @var Factura $factura */
                                $factura = $this->getRecord();
                                $pagadoEnDB = $factura->pagos()->sum('monto');
                                $pagosEnFormulario = $livewire->data['pagos']; // Acceder directamente al estado del Livewire

                                $montoRecibidoAcumulado = 0;
                                foreach ($pagosEnFormulario as $index => $item) {
                                    // Calcular el 'monto a cubrir' para este ítem específico
                                    $itemMontoACubrir = round(max(0, $factura->total - $pagadoEnDB - $montoRecibidoAcumulado), 2);

                                    // Calcular el 'cambio' para este ítem específico
                                    $itemMontoRecibido = (float)($item['monto_recibido'] ?? 0);
                                    $itemCambio = $itemMontoRecibido - $itemMontoACubrir;

                                    // Actualizar los campos en el estado del formulario
                                    $set("pagos.{$index}.monto", $itemMontoACubrir);
                                    $set("pagos.{$index}.cambio", round($itemCambio, 2));

                                    // Acumular el monto recibido para el cálculo de los siguientes ítems
                                    $montoRecibidoAcumulado += $itemMontoRecibido;
                                }

                                // 3. Forzar la actualización del placeholder 'total_a_pagar'
                                // Esto es necesario para que el placeholder reaccione a los cambios globales en el repetidor
                                if ($this->form->getComponent('total_a_pagar')) {
                                    $this->form->getComponent('total_a_pagar')->refreshState();
                                }
                            }),

                        Forms\Components\TextInput::make('cambio')
                            ->label('Cambio')
                            ->disabled()
                            ->numeric()
                            ->extraAttributes(function (?string $state) {
                                if (is_numeric($state) && (float)$state < 0) {
                                    return ['class' => 'font-bold text-danger-600 dark:text-danger-400'];
                                }
                                return ['class' => 'font-bold'];
                            })
                            ->formatStateUsing(function (?string $state) {
                                return 'L. ' . number_format((float)$state, 2);
                            }),
                    ])
                    ->minItems(1)
                    ->default(function () {
                        return [[
                            'metodo_pago_id' => null,
                            'monto' => null,
                            'referencia' => '',
                            'monto_recibido' => null,
                            'cambio' => null,
                        ]];
                    })
                    ->columns(6)
                    ->columnSpanFull()
                    ->collapsed(false)
                    ->live() // El repetidor es live
                    ->afterStateUpdated(function (Get $get, Set $set, callable $livewire) {
                        // Cuando se añade/elimina un ítem, forzar la actualización de todos los campos 'monto'
                        // y el placeholder 'total_a_pagar'
                        // Similar a la lógica en monto_recibido, pero para añadir/eliminar items
                        /** @var Factura $factura */
                        $factura = $this->getRecord();
                        $pagadoEnDB = $factura->pagos()->sum('monto');
                        $pagosEnFormulario = $livewire->data['pagos']; // Acceder directamente al estado del Livewire

                        $montoRecibidoAcumulado = 0;
                        foreach ($pagosEnFormulario as $index => $item) {
                            $itemMontoACubrir = round(max(0, $factura->total - $pagadoEnDB - $montoRecibidoAcumulado), 2);
                            $itemMontoRecibido = (float)($item['monto_recibido'] ?? 0);
                            $itemCambio = $itemMontoRecibido - $itemMontoACubrir;

                            $set("pagos.{$index}.monto", $itemMontoACubrir);
                            $set("pagos.{$index}.cambio", round($itemCambio, 2));

                            $montoRecibidoAcumulado += $itemMontoRecibido;
                        }

                        if ($this->form->getComponent('total_a_pagar')) {
                            $this->form->getComponent('total_a_pagar')->refreshState();
                        }
                    }),

            ])
            ->statePath('data')
            ->columns(1)
            ->inlineLabel(false);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('volver_a_factura')
                ->label('Volver a Factura')
                ->color('gray') // Color diferente para distinguirlo
                ->url(FacturaResource::getUrl('view', ['record' => $this->record->id]))
                ->icon('heroicon-o-arrow-uturn-left'), 

            Action::make('submit')
                ->label('Registrar Pago')
                ->action('registrarPago')
                ->color('success')
                ->requiresConfirmation()
                ->extraAttributes(['class' => 'ml-4']),
        ];
    }

    

    public function registrarPago(): void
    {
        $data = $this->form->getState();

        try {
            $factura = $this->record;
            $pagosDB = $factura->pagos()->sum('monto');
            $montoTotalFactura = $factura->total;
            $pagosFormulario = collect($data['pagos']);

            $montoFormularioTotalRecibido = round($pagosFormulario->sum(fn ($p) => floatval($p['monto_recibido'] ?? 0)), 2);
            $montoPendienteInicial = round($montoTotalFactura - $pagosDB, 2);

            if (bccomp((string) $montoFormularioTotalRecibido, (string) $montoPendienteInicial, 2) === -1) {
                throw new \Exception("El monto total recibido (L. {$montoFormularioTotalRecibido}) es menor al saldo pendiente de la factura (L. {$montoPendienteInicial}).");
            }

            $saldoActualFactura = $montoTotalFactura - $pagosDB;

            foreach ($pagosFormulario as $pago) {
                if (empty($pago['monto_recibido']) || (float) $pago['monto_recibido'] <= 0) continue;

                $montoRecibido = round((float)($pago['monto_recibido'] ?? 0), 2);

                $montoAplicadoAFactura = min($montoRecibido, max(0, $saldoActualFactura));

                $cambio = $montoRecibido - $montoAplicadoAFactura;

                Pago::create([
                    'factura_id' => $factura->id,
                    'empresa_id' => $factura->empresa_id,
                    'metodo_pago_id' => $pago['metodo_pago_id'],
                    'monto' => $montoAplicadoAFactura,
                    'referencia' => $pago['referencia'] ?? null, // <--- CAMBIO AQUÍ: Acceso seguro a la referencia
                    'monto_recibido' => $montoRecibido,
                    'cambio' => $cambio,
                ]);

                $saldoActualFactura -= $montoAplicadoAFactura;
            }

            $totalPagadoFinal = round($factura->fresh()->pagos()->sum('monto'), 2);

            if (bccomp((string)$totalPagadoFinal, (string)$montoTotalFactura, 2) >= 0) {
                $factura->update(['estado' => 'Pagada']);
            }

            Notification::make()
                ->success()
                ->title('Pago registrado correctamente.')
                ->send();

            $this->redirect(FacturaResource::getUrl('view', ['record' => $factura]));

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error al registrar pago')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getListeners(): array
    {
        return parent::getListeners();
    }
}

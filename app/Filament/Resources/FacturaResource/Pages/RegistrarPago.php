<?php

namespace App\Filament\Resources\FacturaResource\Pages;

use App\Filament\Resources\FacturaResource;
use App\Models\Factura;
use App\Models\MetodoPago;
use App\Models\Pago;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
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
                'monto' => $restante,
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
                            ->label('Monto')
                            ->numeric()
                            ->required()
                            ->columnSpan(2)
                            ->live(onBlur: true)
                            ->default(function (Get $get) {
                                /** @var Factura $factura */
                                $factura = $this->getRecord();
                                $pagadoEnDB = $factura->pagos()->sum('monto');
                                $pagosEnFormulario = $get('../../pagos') ?? [];
                                $pagadoEnFormulario = collect($pagosEnFormulario)->sum('monto');
                                $restante = $factura->total - $pagadoEnDB - $pagadoEnFormulario;

                                // **SOLUCIÓN 1: Redondear el resultado a 2 decimales**
                                return round(max(0, $restante), 2);
                            }),

                        Forms\Components\TextInput::make('referencia')
                            ->label('Referencia (opcional)')
                            ->columnSpan(2)
                            ->maxLength(50),

                        Forms\Components\TextInput::make('monto_recibido')
                            ->label('Monto Recibido')
                            ->visible(fn (Get $get) => optional(MetodoPago::find($get('metodo_pago_id')))?->nombre === 'Efectivo')
                            ->numeric()
                            ->reactive()
                            ->live(onBlur: true) // solo cuando termina de escribir
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $monto = (float) $get('monto') ?? 0;
                                $recibido = (float) $state ?? 0;

                                // Solo actualiza si es válido
                                if ($recibido >= $monto) {
                                    $set('cambio', round($recibido - $monto, 2));
                                } else {
                                    $set('cambio', 0); // o null si prefieres
                                }
                            }),

                        Forms\Components\TextInput::make('cambio')
                            ->label('Cambio')
                            ->visible(fn (Get $get) => optional(MetodoPago::find($get('metodo_pago_id')))?->nombre === 'Efectivo')
                            ->disabled()
                            ->numeric(),

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
                    // **SOLUCIÓN 2: Mantener los items del repetidor abiertos por defecto**
                    ->collapsed(false),

            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Registrar Pago')
                ->action('registrarPago')
                ->color('success')
                ->requiresConfirmation(),
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

            $montoFormularioTotal = round($pagosFormulario->sum(fn ($p) => floatval($p['monto'] ?? 0)), 2);
            $montoPendiente = round($montoTotalFactura - $pagosDB, 2);

            // Validar monto total
            if ($montoFormularioTotal <= 0) {
                throw new \Exception('Debe ingresar al menos un monto válido.');
            }

            if (bccomp((string) $montoFormularioTotal, (string) $montoPendiente, 2) === -1) {
                throw new \Exception("El monto ingresado (L. {$montoFormularioTotal}) es menor al saldo pendiente (L. {$montoPendiente}).");
            }

            // 🔐 PRIMERA PASADA: Validar todos los pagos SIN guardar
            foreach ($pagosFormulario as $pago) {
                if (empty($pago['monto']) || (float) $pago['monto'] <= 0) continue;

                $monto = round((float)($pago['monto'] ?? 0), 2);
                $recibido = round((float)($pago['monto_recibido'] ?? 0), 2);
                $metodo = MetodoPago::find($pago['metodo_pago_id']);

                if ($metodo?->nombre === 'Efectivo') {
                    if ($recibido < $monto) {
                        throw new \Exception("El monto recibido en efectivo (L. {$recibido}) es menor al monto a pagar (L. {$monto}).");
                    }
                }
            }

            // ✅ SEGUNDA PASADA: Ya validado, ahora sí guardar
            foreach ($pagosFormulario as $pago) {
                if (empty($pago['monto']) || (float) $pago['monto'] <= 0) continue;

                $monto = round((float)($pago['monto'] ?? 0), 2);
                $recibido = round((float)($pago['monto_recibido'] ?? 0), 2);
                $cambio = null;

                $metodo = MetodoPago::find($pago['metodo_pago_id']);

                if ($metodo?->nombre === 'Efectivo') {
                    $cambio = round(max($recibido - $monto, 0), 2);
                }

                Pago::create([
                    'factura_id' => $factura->id,
                    'empresa_id' => $factura->empresa_id,
                    'metodo_pago_id' => $pago['metodo_pago_id'],
                    'monto' => $monto,
                    'referencia' => $pago['referencia'],
                    'monto_recibido' => $pago['monto_recibido'] ?? null,
                    'cambio' => $cambio,
                ]);
            }

            // Estado de la factura
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

}
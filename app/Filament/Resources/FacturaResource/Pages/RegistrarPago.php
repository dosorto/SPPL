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
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\DB;


class RegistrarPago extends EditRecord
{
    protected static string $resource = FacturaResource::class;
    protected static ?string $title = 'Métodos de Pago';
    protected static string $view = 'filament.resources.factura-resource.pages.registrar-pago';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var \App\Models\Factura $factura */
        $factura = $this->getRecord()->fresh();

        // Si la factura ya no está pendiente, bloquear y redirigir a la vista
        if ($factura->estado !== 'Pendiente') {
            Notification::make()
                ->title('No editable')
                ->body(sprintf(
                    'Esta %s ya está %s.',
                    $factura->cai_id ? 'factura' : 'orden de compra',
                    $factura->estado
                ))
                ->warning()
                ->send();

            $this->redirect(\App\Filament\Resources\FacturaResource::getUrl('view', [
                'record' => $factura->id,
            ]));
            return;
        }

        // Precargar importes para el formulario
        $pagado   = $factura->pagos()->sum('monto');
        $restante = round(max(0, $factura->total - $pagado), 2);

        $this->form->fill([
            'usar_cai'        => true,
            'es_orden_compra' => false,
            'pagos' => [[
                'metodo_pago_id' => null,
                'monto'          => $restante,
                'referencia'     => '',
                'monto_recibido' => null,
                'cambio'         => null,
            ]],
        ]);
    }


    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([

            Toggle::make('usar_cai')
                ->label('Emitir Factura (con CAI)')
                ->default(false)           // <- antes true
                ->reactive()
                ->afterStateUpdated(function (bool $state, callable $set) {
                    // Si activo “usar_cai”, desactivo “es_orden_compra”
                    $set('es_orden_compra', ! $state);
                })
                ->columnSpanFull(),

            Toggle::make('es_orden_compra')
                ->label('Emitir Orden de Compra (sin CAI)')
                ->default(false)           // <- seguimos en false
                ->reactive()
                ->afterStateUpdated(function (bool $state, callable $set) {
                    // Si activo “es_orden_compra”, desactivo “usar_cai”
                    $set('usar_cai', ! $state);
                })
                ->columnSpanFull(),


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
                            ->default(fn (Get $get) => round(
                                // monto default - monto a cubrir
                                (($get('monto_recibido') ?? 0) - ($get('monto') ?? 0)),
                                2,
                            ))
                            ->live()                    // para que se refresque cuando cambie estado
                            ->extraAttributes(function (?string $state) {
                                if (is_numeric($state) && (float)$state < 0) {
                                    return ['class' => 'font-bold text-danger-600 dark:text-danger-400'];
                                }
                                return ['class' => 'font-bold'];
                            })
                            ->formatStateUsing(fn (?string $state) => 'L. ' . number_format((float)$state, 2))
                            ->afterStateHydrated(function ($component, Get $get, Set $set) {
                                // recalculamos al inicio también
                                $monto    = (float) ($get('monto') ?? 0);
                                $recibido = (float) ($get('monto_recibido') ?? 0);
                                $set('cambio', round($recibido - $monto, 2));
                            }),


                        Forms\Components\TextInput::make('referencia')
                            ->label('Referencia (opcional)')
                            ->columnSpan(2)
                            ->maxLength(50)
                            ->visible(fn (Get $get) => in_array(
                                optional(MetodoPago::find($get('metodo_pago_id')))->nombre,
                                ['Tarjeta de Crédito','Tarjeta de Débito','Transferencia Bancaria']
                            )),
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
                ->label('Regresar a Edición')
                ->color('warning')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => 
                    $this->getRecord()->estado === 'Pendiente'
                        ? FacturaResource::getUrl('edit-pendiente', ['record' => $this->getRecord()->getKey()])
                        : FacturaResource::getUrl('view', ['record' => $this->getRecord()->getKey()])
                ),

            Action::make('ver_factura')
                ->label('Vista Detallada')
                ->color('gray')
                ->icon('heroicon-o-eye')
                ->url(FacturaResource::getUrl('view', ['record' => $this->record->id])),


            Action::make('submit')
            ->label('Registrar Pago')
            ->icon('heroicon-o-credit-card')
            ->color('success')

            // Solo muestra la modal si hay una opción elegida
            ->requiresConfirmation(function () {
                $s = $this->form->getState();
                return (bool) (($s['usar_cai'] ?? false) || ($s['es_orden_compra'] ?? false));
            })

            // Texto de la modal
            ->modalHeading('Confirmar registro de pago')
            ->modalDescription(function () {
                $f     = $this->getRecord();
                $saldo = round($f->total - $f->pagos()->sum('monto'), 2);
                return 'Se registrará el pago para este documento. Saldo a cubrir: L. ' . number_format($saldo, 2);
            })

            // Handler que valida otra vez y registra
            ->action('onRegistrarPagoClicked'),
        ];
    }

    public function onRegistrarPagoClicked(): void
    {
        $state = $this->form->getState();

        $usarCai   = (bool) ($state['usar_cai'] ?? false);
        $esOrden   = (bool) ($state['es_orden_compra'] ?? false);

        if (! $usarCai && ! $esOrden) {
            Notification::make()
                ->title('Selecciona el tipo de emisión')
                ->body('Debes elegir “Emitir Factura (con CAI)” o “Emitir Orden de Compra (sin CAI)”.')
                ->danger()
                ->send();
            return;
        }

        // Seguridad extra: si por alguna razón ambos están activos, priorizamos el toggle recién marcado
        if ($usarCai && $esOrden) {
            Notification::make()
                ->title('Configuración inválida')
                ->body('Solo puedes seleccionar una opción: Factura con CAI o Orden de Compra.')
                ->danger()
                ->send();
            return;
        }

        // Todo ok → ejecuta el registro real
        $this->registrarPago();
    }


    public function registrarPago(): void
    {
        $data    = $this->form->getState();
        /** @var Factura $factura */
        $factura = $this->record;

        try {
            DB::transaction(function () use ($factura, $data) {
                //
                // 1) Validación de montos
                //
                $pagosDB            = $factura->pagos()->sum('monto');
                $totalFactura       = $factura->total;
                $recibidoFormulario = round(collect($data['pagos'])->sum(fn ($p) => floatval($p['monto_recibido'] ?? 0)), 2);
                $saldoInicial       = round($totalFactura - $pagosDB, 2);

                if (bccomp((string)$recibidoFormulario, (string)$saldoInicial, 2) === -1) {
                    throw new \Exception("Lo recibido (L. {$recibidoFormulario}) es menor al saldo (L. {$saldoInicial}).");
                }

                //
                // 2) Registro de cada Pago y cálculo de cambio
                //
                $saldoActual = $saldoInicial;
                foreach ($data['pagos'] as $pago) {
                    $recibido = round((float)($pago['monto_recibido'] ?? 0), 2);
                    if ($recibido <= 0) {
                        continue;
                    }

                    $aplicado = min($recibido, $saldoActual);
                    $cambio   = round($recibido - $aplicado, 2);

                    Pago::create([
                        'factura_id'     => $factura->id,
                        'empresa_id'     => $factura->empresa_id,
                        'metodo_pago_id' => $pago['metodo_pago_id'],
                        'monto'          => $aplicado,
                        'monto_recibido' => $recibido,
                        'cambio'         => $cambio,
                        'referencia'     => $pago['referencia'] ?? null,
                    ]);

                    $saldoActual -= $aplicado;
                }

                //
                // 3) Asignación de folio y estado
                //
                if (! empty($data['usar_cai'])) {
                    $cai = \App\Models\Cai::obtenerCaiSeguro($factura->empresa_id)
                        ?? throw new \Exception('No hay un CAI activo para esta empresa.');

                    if (is_null($factura->cai_id)) {
                        $cai->increment('numero_actual');
                    }

                    $folio         = $cai->numero_actual;
                    $folioPadded   = str_pad($folio, 8, '0', STR_PAD_LEFT);
                    $numeroFactura = "{$cai->establecimiento}-{$cai->punto_emision}-{$cai->tipo_documento}-{$folioPadded}";

                    $factura->numero_factura = $numeroFactura;
                    $factura->cai_id         = $cai->id;
                    $factura->estado         = 'Pagada';
                } else {
                    $factura->numero_factura = (string) $factura->id;
                    $factura->cai_id         = null;
                    $factura->estado         = 'Pagada';
                }

                //
                // 4) Asignación de empleado
                //
                $user     = auth()->user()->load('persona.empleado');
                $empleado = $user->persona->empleado ?? null;

                if (! $empleado) {
                    throw new \Exception("No se encontró un empleado asociado a este usuario.");
                }

                if (! $factura->empleado_id) {
                    $factura->empleado_id = $empleado->id;
                }

                // Guardamos todos los cambios finales en la factura
                $factura->save();
            });

            Notification::make()
                ->success()
                ->title('Pago registrado correctamente.')
                ->send();

            $this->redirect(FacturaResource::getUrl('view', ['record' => $factura->id]));
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

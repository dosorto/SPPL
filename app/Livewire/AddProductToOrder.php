<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class AddProductToOrder extends Component implements HasForms
{
    use InteractsWithForms;

    public $detalles = [];
    public $tipo_orden_compra_id;

    public function mount($detalles = [], $tipo_orden_compra_id = null): void
    {
        $this->detalles = $detalles;
        $this->tipo_orden_compra_id = $tipo_orden_compra_id;
        \Log::info('AddProductToOrder inicializado', ['detalles' => $this->detalles, 'tipo_orden_compra_id' => $this->tipo_orden_compra_id]);
        $this->form->fill([
            'producto_id' => null,
            'cantidad' => 1,
            'precio' => 0,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('producto_id')
                ->label('Producto')
                ->required()
                ->searchable()
                ->preload()
                ->optionsLimit(15) // Limit to 15 products
                ->getSearchResultsUsing(function (string $search) {
                    $query = \App\Models\Productos::with(['categoria', 'subcategoria'])
                        ->where(function ($q) use ($search) {
                            $q->where('nombre', 'like', "%{$search}%")
                              ->orWhere('codigo', 'like', "%{$search}%")
                              ->orWhere('sku', 'like', "%{$search}%");
                        });
                    if ($this->tipo_orden_compra_id) {
                        $tipoOrden = \App\Models\TipoOrdenCompras::with(['categoria', 'subcategoria'])->find($this->tipo_orden_compra_id);
                        if ($tipoOrden) {
                            if ($tipoOrden->categoria_id) {
                                $query->where('categoria_id', $tipoOrden->categoria_id);
                            }
                            if ($tipoOrden->subcategoria_id) {
                                $query->where('subcategoria_id', $tipoOrden->subcategoria_id);
                            }
                        }
                    }
                    if (Auth::check() && !Auth::user()->hasRole('root')) {
                        $query->where('empresa_id', Auth::user()->empresa_id);
                    }
                    $results = $query->limit(15)->pluck('nombre', 'id');
                    return $results->isEmpty() ? [] : $results;
                })
                ->getOptionLabelFromRecordUsing(function ($record) {
                    return sprintf(
                        '%s (Categoría: %s, Subcategoría: %s, SKU: %s)',
                        $record->nombre,
                        optional($record->categoria)->nombre ?? 'Sin categoría',
                        optional($record->subcategoria)->nombre ?? 'Sin subcategoría',
                        $record->sku
                    );
                })
                ->reactive()
                ->rules(['required', 'exists:productos,id']), // Explicit validation
            Forms\Components\TextInput::make('cantidad')
                ->label('Cantidad')
                ->required()
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->reactive()
                ->rules(['required', 'numeric', 'min:1']),
            Forms\Components\TextInput::make('precio')
                ->label('Precio Unitario')
                ->required()
                ->numeric()
                ->prefix('HNL')
                ->default(0)
                ->reactive()
                ->rules(['required', 'numeric', 'min:0']),
            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('add_product')
                    ->label('Añadir Producto')
                    ->action(function (Forms\Components\Component $component) {
                        // Validate form explicitly
                        $this->form->validate();
                        $state = $this->form->getState();
                        $this->detalles[] = [
                            'producto_id' => $state['producto_id'],
                            'cantidad' => $state['cantidad'],
                            'precio' => $state['precio'],
                            'created_by' => Auth::id() ?: null,
                            'updated_by' => Auth::id() ?: null,
                            'deleted_by' => null,
                        ];
                        \Log::info('Producto añadido', ['detalles' => $this->detalles]);
                        // Update parent form state
                        $component->getLivewire()->getForm()->fill([
                            'detalles' => $this->detalles,
                        ]);
                        // Reset form fields
                        $this->form->fill([
                            'producto_id' => null,
                            'cantidad' => 1,
                            'precio' => 0,
                        ]);
                    })
                    ->button()
                    ->color('primary'),
            ]),
        ];
    }

    public function render()
    {
        return view('livewire.add-product-to-order');
    }
}
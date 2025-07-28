{{-- resources/views/livewire/wrap-orden-compra-detalles-form.blade.php --}}
@if (isset($record) && $record->id)
    @livewire('orden-compra-detalles-form', ['ordenCompraId' => $record->id])
@else
    @livewire('orden-compra-detalles-form')
@endif

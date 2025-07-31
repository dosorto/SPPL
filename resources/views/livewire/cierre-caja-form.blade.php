<div>
    <h2>Cierre de Caja</h2>
    <form wire:submit.prevent="cerrarApertura">
        <input type="hidden" wire:model="apertura_id">
        <button type="submit">Cerrar Apertura</button>
    </form>
    @if(session()->has('success'))
        <div>{{ session('success') }}</div>
    @endif
    @if(session()->has('error'))
        <div>{{ session('error') }}</div>
    @endif
</div>

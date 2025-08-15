<x-filament-panels::page.simple>
    <x-slot name="heading">
        <div class="flex justify-center">
            <img src="{{ asset('images/JADE.png') }}" alt="JADEH" class="h-16" />
        </div>
    </x-slot>

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page.simple>

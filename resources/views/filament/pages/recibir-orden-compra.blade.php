<x-filament-panels::page>
    {{ $this->form }}
    
    <div class="mt-6 flex justify-end">
        <x-filament-actions::actions 
            :actions="$this->getActions()" 
            alignment="right"
        />
    </div>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
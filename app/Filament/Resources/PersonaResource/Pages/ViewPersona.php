<?php

namespace App\Filament\Resources\PersonaResource\Pages;

use App\Filament\Resources\PersonaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Form;

class ViewPersona extends ViewRecord
{
    protected static string $resource = PersonaResource::class;

    // Mostrar fotografía si existe, si no mostrar mensaje personalizado
    public function getFormSchema(): array
    {
        // Tomar el schema original y filtrar cualquier FileUpload de fotografia
        $schema = array_filter(
            parent::getFormSchema(),
            fn ($component) => !($component instanceof Forms\Components\FileUpload && $component->getName() === 'fotografia')
        );
        $schema = array_values($schema); // Reindexar
        $record = $this->getRecord();

        if (optional($record)->fotografia) {
            array_unshift($schema, Forms\Components\Placeholder::make('fotografia')
                ->label('Fotografía')
                ->content(fn () => '<img src="' . asset('storage/' . optional($record)->fotografia) . '" style="max-width:150px;max-height:150px;border-radius:8px;">')
                ->columnSpanFull()
                ->extraAttributes(['style' => 'text-align:center'])
                ->html());
        } else {
            array_unshift($schema, Forms\Components\Placeholder::make('fotografia')
                ->label('Fotografía')
                ->content('Fotografía no agregada')
                ->columnSpanFull()
                ->extraAttributes(['style' => 'text-align:center;color:#888'])
                ->html());
        }

        return $schema;
    }
}

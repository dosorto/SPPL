<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\View;
use Filament\Pages\Actions\EditAction;

class ViewNomina extends ViewRecord
{
    protected static string $resource = NominaResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->columns(2);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Datos generales de la nómina')
                ->icon('heroicon-o-clipboard-document')
                ->schema([
                    Placeholder::make('empresa')
                        ->label('Empresa')
                        ->content(fn () => $this->record->empresa?->nombre ?? 'N/A'),
                    Placeholder::make('mes')
                        ->label('Mes')
                        ->content(fn () => $this->record->mes ?? 'N/A'),
                    Placeholder::make('año')
                        ->label('Año')
                        ->content(fn () => $this->record->año ?? 'N/A'),
                    Placeholder::make('descripcion')
                        ->label('Descripción')
                        ->content(fn () => $this->record->descripcion ?? 'N/A'),
                    Placeholder::make('cerrada')
                        ->label('Estado')
                        ->content(fn () => $this->record->cerrada ? 'Cerrada' : 'Abierta'),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Historial de Pagos de Empleados')
                ->icon('heroicon-o-user-group')
                ->schema([
                    View::make('filament.nomina.tabla-empleados')
                        ->viewData([
                            'empleados' => $this->record->detalleNominas,
                        ])
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => !$this->record->cerrada),
                
            \Filament\Actions\Action::make('cerrarNomina')
                ->label('Cerrar Nómina')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(fn () => !$this->record->cerrada)
                ->requiresConfirmation()
                ->modalHeading('Cerrar Nómina')
                ->modalDescription('Una vez cerrada la nómina, no podrás editarla ni eliminarla. ¿Estás seguro de que deseas cerrarla?')
                ->modalSubmitActionLabel('Sí, cerrar nómina')
                ->action(function () {
                    $this->record->update([
                        'cerrada' => true
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Nómina cerrada')
                        ->body('La nómina y su historial de pagos han sido cerrados exitosamente.')
                        ->success()
                        ->send();
                        
                    $this->redirect(route('filament.admin.resources.nominas.view', $this->record));
                }),
        ];
    }
}

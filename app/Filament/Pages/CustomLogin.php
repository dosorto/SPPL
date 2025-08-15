<?php

namespace App\Filament\Pages;

use Filament\Pages\Auth\Login as BasePage;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class CustomLogin extends BasePage
{
    protected static string $view = 'filament.pages.auth.login-custom-alt';
    
    // Sobrescribir el título
   // public function getTitle(): string|Htmlable
   // {
   //     return 'JADEH';
   // }

    // Personalizar el campo de email
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Correo Electrónico')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus();
    }
    
    // Personalizar el campo de contraseña
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Contraseña')
            ->password()
            ->required();
    }
    
    // Personalizar el botón de inicio de sesión
    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Iniciar sesión')
            ->submit('authenticate')
            ->color('warning');
    }
    
    public function getHeading(): string|Htmlable
    {
        return '';
    }
}

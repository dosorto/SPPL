@php
    $heading = $this->getHeading();
    $cardWidth = 'md';
@endphp

<x-filament-panels::page.simple class="login-page !p-0 !m-0 !bg-transparent">
    <div class="h-screen w-screen flex items-center justify-center fixed inset-0 overflow-hidden">
        
        <!-- Efecto de burbujas flotantes -->
        <div class="bubbles-container">
            @for ($i = 1; $i <= 15; $i++)
                <div class="bubble"></div>
            @endfor
        </div>
        
        <!-- Tarjeta de login con efecto cristal -->
        <div class="max-w-md w-full z-10 glass-card mx-auto">
            <div class="flex justify-center mb-4">
                <img src="https://jadehsystem.com/images/Logo.png" alt="JADEH" class="h-6 w-16 object-contain" />
                <img src="https://jadehsystem.com/images/Logo.png" alt="JADEH" class="h-6 w-16 object-contain" />
                <img src="https://jadehsystem.com/images/Logo.png" alt="JADEH" class="h-6 w-16 object-contain" />
                <img src="https://jadehsystem.com/images/Logo.png" alt="JADEH" class="h-6 w-16 object-contain" />
                <img src="https://jadehsystem.com/images/Logo.png" alt="JADEH" class="h-6 w-16 object-contain" />
                <img src="https://jadehsystem.com/images/Logo.png" alt="JADEH" class="h-6 w-16 object-contain" />
            </div>
            
            <div class="mb-4 text-center">
                <h2 class="text-xl font-bold text-gray-800">Bienvenido</h2>
                <p class="text-sm text-gray-600">Ingresa tus credenciales para continuar</p>
            </div>
            
            <!-- Formulario -->
            <x-filament-panels::form wire:submit="authenticate">
                {{ $this->form }}
                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>
        </div>
    </div>
    
    <style>
        /* Inputs siempre visibles, fondo blanco y opacidad 1 en todos los estados */
        .fi-input,
        .filament-forms-text-input-component input,
        .filament-forms-password-input-component input,
        .fi-input:empty,
        .fi-input:focus,
        .fi-input:not(:focus),
        .fi-input[placeholder],
        .fi-input:-webkit-autofill {
            background: #fff !important;
            opacity: 1 !important;
            border: 2px solid #166534 !important;
            color: #222 !important;
        }
        /* Labels grandes y gruesos para los campos principales */
            .fi-fo-field-wrp-label,
            .fi-fo-field-wrp-label[style],
            .fi-fo-field-wrp-label:empty,
            .fi-fo-field-wrp-label *,
            .fi-fo-field-wrp-label::before,
            .fi-fo-field-wrp-label::after {
                font-size: 1.05rem !important; /* ligeramente más pequeña */
                font-weight: 700 !important;
                color: #166534 !important; /* verde oscuro */
                opacity: 1 !important;
                text-shadow: none !important;
                border: none !important;
                background: none !important;
                padding: 0 !important;
            }
            /* Letra más pequeña para 'Recordarme' */
            label[for="data.remember"] span {
                font-size: 0.85rem !important;
            }
        /* Borde verde y fondo blanco para los campos de entrada */
        .fi-input,
        .filament-forms-text-input-component input,
        .filament-forms-password-input-component input {
            border: 2px solid #166534 !important;
            background: #fff !important;
            border-radius: 0.5rem !important;
            color: #166534 !important; /* verde oscuro */
            font-size: 0.95rem !important; /* más pequeña que label */
            font-weight: 600 !important;
            box-shadow: none !important;
        }
        /* Restaurar el fondo y borde del checkbox */
        input[type="checkbox"] {
            background-color: #fff !important;
            border: 2px solid #166534 !important;
            width: 1.25rem !important;
            height: 1.25rem !important;
            border-radius: 0.25rem !important;
            box-shadow: none !important;
            appearance: none !important;
            cursor: pointer !important;
            position: relative;
        }
        input[type="checkbox"]:checked {
            background-color: #166534 !important;
            border-color: #14532d !important;
        }
        input[type="checkbox"]:checked::after {
            content: "";
            display: block;
            position: absolute;
            top: 0.25rem;
            left: 0.375rem;
            width: 0.375rem;
            height: 0.75rem;
            border: solid #fff;
            border-width: 0 0.2em 0.2em 0;
            transform: rotate(45deg);
        }
        /* Labels de Correo Electrónico y Contraseña */
        label[for="data.email"], label[for="data.password"] {
            font-size: 1.25rem !important; /* text-xl */
            font-weight: 700 !important;
        }
        /* Reset Filament layout */
        .filament-main, .filament-main-content {
            padding: 0 !important;
            margin: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background-color: transparent !important;
        }
        
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
            background-color: transparent !important;
        }
        
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -10;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            max-width: 400px;
            width: 90%;
            max-height: 480px;
            overflow-y: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        /* Compactar formulario */
        .filament-form > div > div { margin-bottom: 0.5rem !important; }
        .filament-form label { margin-bottom: 0.125rem !important; font-size: 0.875rem !important; }
        .filament-forms-field-wrapper { margin: 0.25rem 0 !important; }
        .filament-forms-text-input-component input,
        .filament-forms-password-input-component input {
            padding: 0.5rem !important;
        }
        
        button[type="submit"] {
            margin-top: 0.5rem !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 12px rgba(22,163,74,0.2) !important; /* verde oscuro */
            background-color: #166534 !important; /* verde Tailwind 800 */
            color: #fff !important;
            font-size: 1.125rem !important; /* text-lg */
            font-weight: 600 !important;
        }
        button[type="submit"]:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(22,163,74,0.3) !important;
            background-color: #14532d !important; /* verde aún más oscuro */
        }
        
        /* Efecto de burbujas */
        .bubbles-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0; left: 0;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }
        
        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            opacity: 0.6;
            animation: rise 15s infinite ease-in;
        }
        
        /* Tamaños y posiciones */
        .bubble:nth-child(1) { width: 40px; height: 40px; left: 10%; animation-duration: 8s; }
        .bubble:nth-child(2) { width: 20px; height: 20px; left: 20%; animation-duration: 5s; animation-delay: 1s; }
        .bubble:nth-child(3) { width: 50px; height: 50px; left: 35%; animation-duration: 10s; animation-delay: 2s; }
        .bubble:nth-child(4) { width: 80px; height: 80px; left: 50%; animation-duration: 7s; }
        .bubble:nth-child(5) { width: 35px; height: 35px; left: 55%; animation-duration: 6s; animation-delay: 1s; }
        .bubble:nth-child(6) { width: 45px; height: 45px; left: 65%; animation-duration: 8s; animation-delay: 3s; }
        .bubble:nth-child(7) { width: 25px; height: 25px; left: 75%; animation-duration: 7s; animation-delay: 2s; }
        .bubble:nth-child(8) { width: 30px; height: 30px; left: 80%; animation-duration: 6s; animation-delay: 1s; }
        .bubble:nth-child(9) { width: 15px; height: 15px; left: 70%; animation-duration: 9s; }
        .bubble:nth-child(10) { width: 50px; height: 50px; left: 85%; animation-duration: 5s; animation-delay: 3s; }
        .bubble:nth-child(11) { width: 25px; height: 25px; left: 15%; animation-duration: 9s; animation-delay: 1.5s; }
        .bubble:nth-child(12) { width: 60px; height: 60px; left: 40%; animation-duration: 8.5s; animation-delay: 2.5s; }
        .bubble:nth-child(13) { width: 15px; height: 15px; left: 60%; animation-duration: 6.5s; animation-delay: 1.5s; }
        .bubble:nth-child(14) { width: 40px; height: 40px; left: 30%; animation-duration: 7.5s; animation-delay: 3.5s; }
        .bubble:nth-child(15) { width: 30px; height: 30px; left: 90%; animation-duration: 6s; animation-delay: 2.5s; }
        
        @keyframes rise {
            0% { bottom: -100px; transform: translateX(0); }
            50% { transform: translateX(100px); }
            100% { bottom: 100%; transform: translateX(-100px); }
        }
        
        /* Inputs transparentes estilo cristal */
        input {
            border-radius: 0.5rem !important;
            background-color: rgba(255, 255, 255, 0.8) !important;
            border: none !important;
            transition: all 0.2s ease;
        }
        
        input:focus {
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.3) !important;
            background-color: rgba(255, 255, 255, 0.95) !important;
        }
        
        /* Quitar fondos y bordes de Filament */
        .filament-panels-page,
        .filament-login-page,
        .fi-auth-card,
        .filament-main,
        .filament-main-content,
        .filament-forms-auth-card,
        .filament-forms-page,
        .filament-forms-card,
        .filament-forms-content,
        .filament-forms-component,
        .filament-forms-container,
        .fi-simple-page > .fi-auth-card {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
        }

        /* Quitar fondo blanco de campos */
        .fi-input-wrp,
        .fi-input-wrp-input,
        .fi-input,
        .bg-white,
        .bg-white\/0 {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
    </style>
</x-filament-panels::page.simple>

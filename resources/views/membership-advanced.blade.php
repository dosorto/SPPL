<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JADEH - Plan Avanzado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .nav-scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(8px);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

    {{-- Navbar (Copiado de welcome.blade.php) --}}
    <nav id="navbar" class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-white bg-opacity-70 backdrop-blur-sm transition-all duration-300 border-b border-gray-200">
        <div class="container mx-auto max-w-7xl flex justify-between items-center">
            <a href="{{ route('welcome') }}" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="JADEH Logo" class="mr-3 w-24">
            </a>
            <div class="space-x-4 flex items-center">
                <a href="{{ route('welcome') }}#inicio" class="px-4 py-2 text-blue-700 hover:bg-blue-100 rounded-full transition font-semibold">Inicio</a>
                <a href="{{ route('welcome') }}#modulos" class="px-4 py-2 text-blue-700 hover:bg-blue-100 rounded-full transition font-semibold">Módulos</a>
                <a href="{{ route('welcome') }}#planes" class="px-4 py-2 text-blue-700 hover:bg-blue-100 rounded-full transition font-semibold">Planes</a>
                <a href="{{ route('contacto') }}" class="px-4 py-2 text-blue-700 hover:bg-blue-100 rounded-full transition font-semibold">Contacto</a>
                <a href="{{ url('/admin/login') }}" class="px-4 py-2 bg-blue-700 text-white rounded-full hover:bg-blue-600 transition font-semibold">Login</a>
            </div>
        </div>
    </nav>

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="container mx-auto p-8 bg-white rounded-lg shadow-xl max-w-2xl mt-24">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-extrabold text-blue-800 mb-2">Plan Avanzado</h1>
                <p class="text-lg text-gray-600">Perfecto para plantas en crecimiento.</p>
            </div>
            
            <form action="{{ route('membresia.solicitud') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="plan_name" value="Plan Avanzado">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Tu Nombre</label>
                    <input type="text" id="name" name="name" required
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="text-center">
                    <button type="submit"
                            class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 transition-all shadow-lg text-lg">
                        Solicitar este Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Footer (Copiado de welcome.blade.php) --}}
    <footer class="bg-gray-900 text-gray-400 py-8 text-center text-sm">
        <div class="container mx-auto">
            <p>&copy; 2025 JADEH. Todos los derechos reservados.</p>
            <p class="mt-1">San Marcos de Colon, Choluteca Department, Honduras</p>
        </div>
    </footer>
    
    <script>
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('nav-scrolled');
            } else {
                navbar.classList.remove('nav-scrolled');
            }
        });
    </script>
</body>
</html>
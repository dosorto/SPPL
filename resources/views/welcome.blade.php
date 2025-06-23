<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPPL - Sistema de Gestión</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-600 to-indigo-700 min-h-screen text-white">

    {{-- Navbar --}}
    <nav class="flex justify-between items-center p-5 bg-opacity-40 bg-black backdrop-blur-sm">
        <h1 class="text-2xl font-bold tracking-widest">SPPL</h1>
        <div class="space-x-4">
            <a href="{{ url('/') }}" class="px-4 py-2 bg-white text-blue-700 rounded-full hover:bg-gray-100 transition">Inicio</a>
            <a href="{{ url('/admin/login') }}" class="px-4 py-2 border border-white rounded-full hover:bg-white hover:text-blue-700 transition">Login</a>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="flex flex-col items-center justify-center text-center mt-32 px-6">
        <h2 class="text-4xl md:text-5xl font-extrabold mb-4 drop-shadow-lg">Bienvenido a SPPL</h2>
        <p class="text-lg md:text-xl mb-6 max-w-2xl">Optimiza tus procesos con nuestra plataforma diseñada para gestión eficiente y moderna.</p>
        <a href="{{ url('/admin/login') }}" class="px-6 py-3 bg-white text-blue-700 font-semibold rounded-full hover:bg-gray-100 transition-all shadow-lg">Empezar ahora</a>
    </section>

    {{-- Footer --}}
    <footer class="absolute bottom-4 w-full text-center text-sm text-white opacity-75">
        &copy; {{ date('Y') }} SPPL. Todos los derechos reservados.
    </footer>

</body>
</html>

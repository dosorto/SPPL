<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPPL - Gestión Integral para Plantas Lácteas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pricing-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        .hero {
            background: linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.7)), url('https://images.unsplash.com/photo-1603415526960-f7e04188794a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
        }
        .feature-icon {
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .nav-scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(8px);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .section-nav-link.active {
            position: relative;
            font-weight: 700;
            color: #1e40af; /* Color azul más oscuro para el activo */
        }
        .section-nav-link.active::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -5px;
            transform: translateX(-50%);
            width: 50%;
            height: 3px;
            background-color: #1e40af;
            border-radius: 9999px;
        }
        .scroll-reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .scroll-reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    {{-- Navbar --}}
    <nav id="navbar" class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-white bg-opacity-70 backdrop-blur-sm transition-all duration-300 border-b border-gray-200">
        <div class="container mx-auto max-w-7xl flex justify-between items-center">
            <a href="#inicio" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="SPPL Logo" class="mr-3 w-24">
        
            </a>
            <div class="space-x-4 flex items-center">
                <a href="#inicio" class="px-4 py-2 text-blue-700 hover:bg-blue-100 rounded-full transition font-semibold section-nav-link">Inicio</a>
                <a href="#modulos" class="px-4 py-2 text-blue-700 hover:bg-blue-100 rounded-full transition font-semibold section-nav-link">Módulos</a>
                <a href="#planes" class="px-4 py-2 text-blue-700 hover:bg-blue-100 rounded-full transition font-semibold section-nav-link">Planes</a>
                <a href="{{ url('/admin/login') }}" class="px-4 py-2 bg-blue-700 text-white rounded-full hover:bg-blue-600 transition font-semibold">Login</a>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section id="inicio" class="hero pt-48 pb-32 px-6 text-center text-gray-800">
        <div class="container mx-auto scroll-reveal">
            <h2 class="text-6xl font-extrabold mb-6 drop-shadow-lg text-blue-900 leading-tight">Gestión Inteligente que Impulsa tu Planta Láctea</h2>
            <p class="text-xl mb-10 max-w-3xl mx-auto font-medium text-gray-700">SPPL es la plataforma integral diseñada para optimizar y controlar cada proceso de tu operación, desde la entrada de la leche cruda hasta la venta del producto final.</p>
            <a href="{{ url('/admin/register') }}" class="px-10 py-4 bg-green-500 text-white font-semibold rounded-full hover:bg-green-600 transition-all shadow-lg text-lg">
                ¡Empieza tu prueba gratuita!
            </a>
        </div>
    </section>

    {{-- Key Modules Section --}}
    <section id="modulos" class="py-20 bg-gray-100">
        <div class="container mx-auto text-center scroll-reveal">
            <h3 class="text-4xl font-extrabold text-blue-800 mb-4">Soluciones diseñadas para tu industria</h3>
            <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto">Nuestros módulos abarcan todas las áreas críticas de tu planta, proporcionando control y visibilidad total.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 px-6 md:px-12">
                {{-- Module 1: Ventas y Clientes --}}
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 scroll-reveal">
                    <div class="feature-icon bg-blue-100 text-blue-700 mx-auto mb-4">
                        <i class="fas fa-cash-register fa-2x"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-blue-800 mb-2">Ventas y Facturación</h4>
                    <p class="text-gray-600">
                        Gestiona tus ventas, emite facturas electrónicas y administra tus clientes para un flujo de caja impecable.
                    </p>
                </div>
                
                {{-- Module 2: Recursos Humanos --}}
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 scroll-reveal">
                    <div class="feature-icon bg-green-100 text-green-700 mx-auto mb-4">
                        <i class="fas fa-users-cog fa-2x"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-blue-800 mb-2">Gestión de Personal</h4>
                    <p class="text-gray-600">
                        Administra nóminas, deducciones y percepciones, y mantén un control preciso sobre tu equipo de trabajo.
                    </p>
                </div>

                {{-- Module 3: Insumos y Materia Prima --}}
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 scroll-reveal">
                    <div class="feature-icon bg-yellow-100 text-yellow-700 mx-auto mb-4">
                        <i class="fas fa-seedling fa-2x"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-blue-800 mb-2">Producción y Trazabilidad</h4>
                    <p class="text-gray-600">
                        Optimiza la gestión de insumos, crea órdenes de producción y garantiza la trazabilidad de cada lote.
                    </p>
                </div>
                
                {{-- Module 4: Inventario de Productos --}}
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 scroll-reveal">
                    <div class="feature-icon bg-red-100 text-red-700 mx-auto mb-4">
                        <i class="fas fa-boxes fa-2x"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-blue-800 mb-2">Control de Inventario</h4>
                    <p class="text-gray-600">
                        Supervisa tus productos lácteos, mantén un inventario preciso en tiempo real y evita desabastecimientos.
                    </p>
                </div>

                {{-- Module 5: Compras y Proveedores --}}
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 scroll-reveal">
                    <div class="feature-icon bg-purple-100 text-purple-700 mx-auto mb-4">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-blue-800 mb-2">Cadena de Suministro</h4>
                    <p class="text-gray-600">
                        Administra proveedores, genera órdenes de compra y asegura un suministro constante de materias primas.
                    </p>
                </div>

                {{-- Module 6: Unidades y Geografía --}}
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 scroll-reveal">
                    <div class="feature-icon bg-teal-100 text-teal-700 mx-auto mb-4">
                        <i class="fas fa-map-marked-alt fa-2x"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-blue-800 mb-2">Datos Maestros</h4>
                    <p class="text-gray-600">
                        Estandariza tus unidades de medida, y gestiona la información geográfica para reportes y logística.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing Cards Section --}}
    <section id="planes" class="py-20 px-6 md:px-12 bg-gray-50">
        <div class="container mx-auto text-center scroll-reveal">
            <h2 class="text-4xl font-extrabold text-blue-800 mb-4">Planes de Membresía a tu medida</h2>
            <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto">Escoge el plan que mejor se adapte al tamaño y las necesidades de tu planta láctea.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                {{-- Card 1: Esencial --}}
                <div class="pricing-card bg-white text-gray-800 p-8 rounded-xl shadow-lg flex flex-col justify-between border-t-4 border-green-500 scroll-reveal">
                    <div>
                        <h3 class="text-2xl font-bold mb-2 text-green-600">Esencial</h3>
                        <p class="text-sm text-gray-500 mb-6">Ideal para plantas que inician la digitalización.</p>
                        <div class="text-5xl font-extrabold mb-4 text-gray-900">$49<span class="text-xl font-normal text-gray-500">/mes</span></div>
                        <ul class="text-left space-y-3 text-gray-700 mb-8">
                            <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i> Gestión básica de producción</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i> Control de inventario limitado</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i> Reportes estándar</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i> Soporte por correo electrónico</li>
                        </ul>
                    </div>
                    <a href="{{ url('/admin/register') }}" class="block w-full text-center py-3 px-6 bg-green-500 text-white font-semibold rounded-full hover:bg-green-600 transition-all">Seleccionar Plan</a>
                </div>

                {{-- Card 2: Avanzado (Recomendado) --}}
                <div class="pricing-card bg-green-600 text-white p-10 rounded-xl shadow-2xl flex flex-col justify-between transform scale-105 border-t-4 border-white scroll-reveal">
                    <div>
                        <h3 class="text-3xl font-bold mb-2 text-white">Avanzado</h3>
                        <p class="text-sm text-green-100 mb-6">El más popular. Perfecto para plantas en crecimiento.</p>
                        <div class="text-6xl font-extrabold mb-4 text-white">$99<span class="text-xl font-normal text-green-200">/mes</span></div>
                        <ul class="text-left space-y-3 text-green-100 mb-8">
                            <li class="flex items-center"><i class="fas fa-check-circle text-white mr-3"></i> Todas las funciones del plan Esencial</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-white mr-3"></i> Gestión avanzada de inventario</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-white mr-3"></i> Informes y analíticas detalladas</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-white mr-3"></i> Soporte prioritario</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-white mr-3"></i> Gestión de lotes y trazabilidad</li>
                        </ul>
                    </div>
                    <a href="{{ url('/admin/register') }}" class="block w-full text-center py-3 px-6 bg-white text-green-600 font-bold rounded-full hover:bg-gray-200 transition-all">Seleccionar Plan</a>
                </div>

                {{-- Card 3: Premium --}}
                <div class="pricing-card bg-white text-gray-800 p-8 rounded-xl shadow-lg flex flex-col justify-between border-t-4 border-green-500 scroll-reveal">
                    <div>
                        <h3 class="text-2xl font-bold mb-2 text-green-600">Premium</h3>
                        <p class="text-sm text-gray-500 mb-6">Soluciones completas para grandes corporaciones.</p>
                        <div class="text-5xl font-extrabold mb-4 text-gray-900">Cotizar</div>
                        <ul class="text-left space-y-3 text-gray-700 mb-8">
                            <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i> Funciones totalmente personalizadas</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i> Integración con otros sistemas</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i> Soporte técnico dedicado 24/7</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i> Formación y consultoría especializada</li>
                        </ul>
                    </div>
                    <a href="{{ url('/contact') }}" class="block w-full text-center py-3 px-6 bg-green-500 text-white font-semibold rounded-full hover:bg-green-600 transition-all">Contactar Ventas</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Call to Action Section --}}
    <section class="py-20 bg-blue-700 text-white text-center">
        <div class="container mx-auto scroll-reveal">
            <h2 class="text-4xl font-extrabold mb-4">¿Listo para transformar tu gestión?</h2>
            <p class="text-lg mb-10 max-w-2xl mx-auto">Únete a SPPL y experimenta una gestión empresarial más eficiente, precisa y rentable. Da el primer paso hoy mismo.</p>
            <a href="{{ url('/admin/register') }}" class="px-10 py-4 bg-green-500 text-white font-semibold rounded-full hover:bg-green-600 transition-all shadow-lg text-lg">
                Regístrate ahora y obtén una demo
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-8 text-center text-sm">
        <div class="container mx-auto">
            <p>&copy; {{ date('Y') }} SPPL. Todos los derechos reservados.</p>
            <p class="mt-1">San Marcos de Colon, Choluteca Department, Honduras</p>
        </div>
    </footer>

    <script>
        // Script para Navbar sticky y cambio de color
        const navbar = document.getElementById('navbar');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('nav-scrolled');
            } else {
                navbar.classList.remove('nav-scrolled');
            }
        });

        // Script para resaltar la sección activa en el Navbar
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.section-nav-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= sectionTop - sectionHeight / 3) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').includes(current)) {
                    link.classList.add('active');
                }
            });
        });

        // Script para efecto de scroll-reveal
        const scrollRevealElements = document.querySelectorAll('.scroll-reveal');

        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.2
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        scrollRevealElements.forEach(el => observer.observe(el));
    </script>

</body>
</html>
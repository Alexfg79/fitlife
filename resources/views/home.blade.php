<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FitLife Gym • Sistema Integral de Entrenamiento y Gestión Fitness</title>
    
    <!-- Metadatos SEO, Accesibilidad y Rendimiento -->
    <meta name="description" content="FitLife Gym • Sistema integral de entrenamiento fitness con carnet digital, 3 sedes oficiales, planes sin matrícula, clases grupales y acceso biométrico inteligente 24/7.">
    <meta name="theme-color" content="#84cc16">
    <meta property="og:title" content="FitLife Gym • Entrenamiento y Gestión Fitness">
    <meta property="og:description" content="Entrena al máximo nivel en nuestras 3 sedes con acceso inteligente, clases grupales y planes sin permanencia obligatoria.">
    <meta property="og:type" content="website">

    <!-- Preconexiones para acelerar carga de recursos críticos -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://images.unsplash.com">

    <!-- Preload de imagen principal del Hero para mejorar LCP -->
    <link rel="preload" as="image" href="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1920&auto=format&fit=crop">

    <!-- Fuentes Tipográficas con display=swap para evitar FOIT -->
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css'])

    <!-- Script de Inicialización de Tema Inmediato (Previene parpadeos FOUC) -->
    <script>
        (function() {
            const appearance = localStorage.getItem('appearance') || 'system';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Alpine.js para interactividad instantánea y accesible -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Instrument Sans', sans-serif;
            overflow-x: hidden;
        }
    </style>
</head>
<body 
    x-data="{ 
        menuMovilAbierto: false, 
        esOscuro: document.documentElement.classList.contains('dark'),
        alternarTema() {
            this.esOscuro = !this.esOscuro;
            if (this.esOscuro) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('appearance', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('appearance', 'light');
            }
        }
    }"
    class="min-h-screen flex flex-col bg-slate-50 text-slate-900 dark:bg-[#090d16] dark:text-[#f8fafc] selection:bg-lime-500 selection:text-zinc-950 transition-colors duration-200"
>

    <!-- =========================================================================
         1. TOP HEADER BAR (Barra Superior de Contacto & Redes en Verde Lima)
         ========================================================================= -->
    <header class="bg-lime-500 text-zinc-950 text-xs font-semibold py-2 px-4 sm:px-8 border-b border-lime-600/30 select-none z-40 relative shadow-xs">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-2">
            <!-- Contacto & Sedes -->
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 sm:gap-6 text-[11px] sm:text-xs tracking-tight">
                <a href="tel:+582129991122" class="flex items-center gap-1.5 hover:text-black transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>+58 (212) 999-FITLIFE (3485)</span>
                </a>
                <a href="mailto:info@fitlife.test" class="hidden sm:flex items-center gap-1.5 hover:text-black transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>contacto@fitlife.test</span>
                </a>
                <div class="flex items-center gap-1.5 text-zinc-900">
                    <svg class="w-3.5 h-3.5 text-zinc-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Sedes Abiertas: Lun a Dom (24/7 Planes Pro • 8:00 a 18:00 Básicos)</span>
                </div>
            </div>

            <!-- Presencia en Sucursales & Redes -->
            <div class="flex items-center gap-4 text-[11px] sm:text-xs">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-black/15 font-bold tracking-wider uppercase text-[10px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-950 animate-ping"></span>
                    3 Sucursales Activas
                </span>
                <div class="hidden lg:flex items-center gap-2">
                    <span class="text-zinc-800">Síguenos:</span>
                    <a href="#contacto" class="hover:text-black transition font-bold" title="Instagram">@FitLifeGym</a>
                    <span>•</span>
                    <a href="#contacto" class="hover:text-black transition font-bold" title="WhatsApp">WhatsApp</a>
                </div>
            </div>
        </div>
    </header>

    <!-- =========================================================================
         2. NAVIGATION BAR (Barra Principal Adaptativa Blanco/Oscuro)
         ========================================================================= -->
    <nav class="sticky top-0 z-40 bg-white/95 dark:bg-slate-950/95 backdrop-blur-md border-b border-slate-200/80 dark:border-zinc-800/80 transition-colors duration-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 h-20 flex items-center justify-between">
            <!-- LOGO: "Fit" en Oscuro/Blanco, "Life" en Verde Lima -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group focus:outline-hidden">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-400 to-lime-600 flex items-center justify-center shadow-[0_0_20px_rgba(132,204,22,0.4)] group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-6 h-6 text-zinc-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6.5 6.5 11 11"/>
                        <path d="m21 21-1-1"/>
                        <path d="m3 3 1 1"/>
                        <path d="m18 22 4-4"/>
                        <path d="m2 6 4-4"/>
                        <path d="m3 10 7-7"/>
                        <path d="m14 21 7-7"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-black tracking-tight leading-none">
                        <span class="text-slate-950 dark:text-white">Fit</span><span class="text-lime-500">Life</span>
                    </span>
                    <span class="text-[9px] font-bold tracking-widest text-slate-500 dark:text-zinc-400 uppercase font-mono mt-0.5">
                        GYM & WELLNESS OS
                    </span>
                </div>
            </a>

            <!-- Menú de Navegación en Mayúsculas (Desktop) -->
            <div class="hidden lg:flex items-center gap-3.5 xl:gap-6" id="nav-desktop-container">
                <a href="#inicio" data-target="inicio" class="nav-link whitespace-nowrap text-xs font-bold uppercase tracking-wider text-lime-600 dark:text-lime-400 transition hover:text-lime-700 dark:hover:text-lime-300">
                    INICIO
                </a>
                <a href="#nosotros" data-target="nosotros" class="nav-link whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 transition">
                    NOSOTROS
                </a>
                <a href="#servicios" data-target="servicios" class="nav-link whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 transition">
                    SERVICIOS
                </a>
                <a href="#clases" data-target="clases" class="nav-link whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 transition">
                    CLASES
                </a>
                <a href="#sucursales" data-target="sucursales" class="nav-link whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 transition">
                    SUCURSALES
                </a>
                <a href="#membresias" data-target="membresias" class="nav-link whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 transition">
                    MEMBRESÍAS
                </a>
                <a href="#contacto" data-target="contacto" class="nav-link whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 transition">
                    CONTACTO
                </a>
            </div>

            <!-- Botones de Acceso / Estado Auth y Tema en la Esquina Derecha -->
            <div class="hidden sm:flex items-center gap-2.5 shrink-0">
                @auth
                    <!-- Usuario Autenticado -->
                    <div class="flex items-center gap-3 bg-slate-100 dark:bg-zinc-900/90 border border-slate-200 dark:border-zinc-800 rounded-full py-1.5 pl-2 pr-4 shadow-inner shrink-0">
                        <div class="w-8 h-8 rounded-full bg-lime-500/20 border border-lime-500/40 flex items-center justify-center text-lime-600 dark:text-lime-400 font-bold text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="text-left text-xs">
                            <span class="block font-bold text-slate-900 dark:text-white leading-tight truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                            <span class="block text-[10px] text-lime-600 dark:text-lime-400 font-mono capitalize">
                                {{ Auth::user()->rol?->nombre ?? 'Usuario' }}
                            </span>
                        </div>
                        <a 
                            href="{{ Auth::user()->esSocio() ? route('mi-perfil') : route('dashboard') }}" 
                            class="ml-2 inline-flex items-center gap-1.5 bg-lime-500 hover:bg-lime-400 text-zinc-950 font-bold text-xs uppercase px-3.5 py-1.5 rounded-full transition shadow-[0_0_15px_rgba(132,204,22,0.3)] whitespace-nowrap"
                        >
                            <span>{{ Auth::user()->esSocio() ? 'MI PERFIL' : 'DASHBOARD' }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                @else
                    <!-- Visitante (Guest) -->
                    <a 
                        href="{{ route('login') }}"
                        class="whitespace-nowrap shrink-0 h-10 px-4 rounded-xl bg-lime-500 hover:bg-lime-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition duration-200 shadow-xs cursor-pointer inline-flex items-center justify-center"
                    >
                        INICIAR SESIÓN
                    </a>
                    <a 
                        href="{{ route('register') }}"
                        class="whitespace-nowrap shrink-0 h-10 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 dark:bg-zinc-900 dark:border-zinc-700/80 dark:text-white dark:hover:border-lime-500/60 dark:hover:text-lime-400 font-bold text-xs uppercase tracking-wider transition duration-200 cursor-pointer inline-flex items-center justify-center"
                    >
                        REGISTRARSE
                    </a>
                @endauth

                <!-- Separador vertical -->
                <div class="h-6 w-px bg-slate-200 dark:bg-zinc-800 shrink-0 mx-0.5"></div>

                <!-- Botón Alternar Modo Claro / Oscuro en la Esquina Extrema Derecha -->
                <button 
                    type="button" 
                    @click="alternarTema()" 
                    class="flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 hover:border-lime-500/60 hover:text-lime-600 dark:hover:text-lime-400 transition-all duration-200 shadow-xs cursor-pointer shrink-0"
                    :title="esOscuro ? 'Cambiar a Tema Blanco / Claro' : 'Cambiar a Tema Oscuro'"
                    aria-label="Cambiar tema de color"
                >
                    <!-- Icono Sol (se muestra en modo oscuro) -->
                    <svg x-show="esOscuro" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Icono Luna (se muestra en modo claro) -->
                    <svg x-show="!esOscuro" x-cloak class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </div>

            <!-- Botón Hamburguesa Móvil -->
            <div class="flex items-center gap-2 lg:hidden">
                <!-- Botón de Tema Móvil -->
                <button 
                    type="button" 
                    @click="alternarTema()" 
                    class="p-2 rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-zinc-900 text-slate-700 dark:text-zinc-300"
                    aria-label="Cambiar tema"
                >
                    <svg x-show="esOscuro" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg x-show="!esOscuro" x-cloak class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <button 
                    type="button" 
                    @click="menuMovilAbierto = !menuMovilAbierto" 
                    class="p-2 rounded-xl bg-slate-100 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-700 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white"
                    aria-label="Abrir Menú"
                >
                    <svg x-show="!menuMovilAbierto" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="menuMovilAbierto" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menú Desplegable Móvil -->
        <div 
            x-show="menuMovilAbierto" 
            x-cloak 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="lg:hidden bg-white dark:bg-zinc-950 border-b border-slate-200 dark:border-zinc-800 px-6 py-6 space-y-4"
        >
            <div class="flex flex-col gap-3 font-bold text-sm tracking-wider uppercase" id="nav-mobile-container">
                <a @click="menuMovilAbierto = false" href="#inicio" data-target="inicio" class="mobile-nav-link text-lime-600 dark:text-lime-400 py-1">INICIO</a>
                <a @click="menuMovilAbierto = false" href="#nosotros" data-target="nosotros" class="mobile-nav-link text-slate-700 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 py-1">NOSOTROS</a>
                <a @click="menuMovilAbierto = false" href="#servicios" data-target="servicios" class="mobile-nav-link text-slate-700 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 py-1">SERVICIOS</a>
                <a @click="menuMovilAbierto = false" href="#clases" data-target="clases" class="mobile-nav-link text-slate-700 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 py-1">CLASES GRUPALES</a>
                <a @click="menuMovilAbierto = false" href="#sucursales" data-target="sucursales" class="mobile-nav-link text-slate-700 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 py-1">SUCURSALES</a>
                <a @click="menuMovilAbierto = false" href="#membresias" data-target="membresias" class="mobile-nav-link text-slate-700 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 py-1">MEMBRESÍAS</a>
                <a @click="menuMovilAbierto = false" href="#contacto" data-target="contacto" class="mobile-nav-link text-slate-700 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 py-1">CONTACTO</a>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex flex-col gap-2.5">
                @auth
                    <a 
                        href="{{ Auth::user()->esSocio() ? route('mi-perfil') : route('dashboard') }}" 
                        class="w-full text-center bg-lime-500 text-zinc-950 font-black py-3 rounded-xl uppercase text-xs tracking-wider"
                    >
                        IR A MI {{ Auth::user()->esSocio() ? 'PERFIL' : 'DASHBOARD' }} ({{ Auth::user()->name }})
                    </a>
                @else
                    <a 
                        href="{{ route('login') }}"
                        class="w-full text-center bg-lime-500 text-zinc-950 font-black py-3 rounded-xl uppercase text-xs tracking-wider shadow-[0_0_15px_rgba(132,204,22,0.4)]"
                    >
                        INICIAR SESIÓN
                    </a>
                    <a 
                        href="{{ route('register') }}"
                        class="w-full text-center bg-slate-100 dark:bg-zinc-900 border border-slate-300 dark:border-zinc-800 text-slate-900 dark:text-white font-bold py-3 rounded-xl uppercase text-xs tracking-wider hover:border-lime-500"
                    >
                        REGISTRARSE
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- =========================================================================
         3. HERO SECTION IMPACTANTE (Diseñado para Tema Claro y Oscuro)
         ========================================================================= -->
    <section id="inicio" class="relative min-h-[85vh] flex items-center justify-center bg-white dark:bg-zinc-950 overflow-hidden py-16 sm:py-24 border-b border-slate-200/80 dark:border-transparent transition-colors duration-200">
        <!-- Imagen de fondo atlética de alta resolución -->
        <div 
            class="absolute inset-0 bg-cover bg-center transition-transform duration-1000 scale-105 opacity-10 dark:opacity-40 mix-blend-multiply dark:mix-blend-luminosity"
            style="background-image: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1920&auto=format&fit=crop');"
        ></div>

        <!-- Overlays Dinámicos & Gradiente Deportivo FitLife -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-50 via-white/80 to-slate-100/60 dark:from-[#090d16] dark:via-zinc-950/80 dark:to-zinc-950/60"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-lime-500/10 dark:from-lime-500/15 via-transparent to-transparent pointer-events-none"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-8 text-center flex flex-col items-center">
            <!-- Badge Pequeño de Categoría -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-lime-500/40 bg-lime-500/10 backdrop-blur-md mb-6 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-lime-500 animate-pulse"></span>
                <span class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-lime-700 dark:text-lime-400">
                    SISTEMA INTEGRAL DE ENTRENAMIENTO Y SALUD
                </span>
            </div>

            <!-- Título Principal Gigante Bold con Acentos Verde Lima -->
            <h1 class="text-4xl sm:text-6xl md:text-7xl font-black text-slate-950 dark:text-white uppercase tracking-tight leading-[1.08] max-w-5xl">
                TRANSFORMA TU <span class="text-lime-600 dark:text-lime-400">CUERPO</span> Y ALCANZA TU <span class="text-lime-600 dark:text-lime-400 underline decoration-lime-500/50 decoration-wavy">MÁXIMO POTENCIAL</span>
            </h1>

            <!-- Párrafo Descriptivo con Tecnología de Carnet Digital -->
            <p class="mt-6 text-base sm:text-xl text-slate-600 dark:text-zinc-300 max-w-3xl leading-relaxed font-normal">
                Disfruta de instalaciones de alto rendimiento y acceso inteligente instantáneo mediante 
                <span class="text-slate-900 dark:text-white font-semibold">carnet digital y código de barras</span>. Entrena con rutinas personalizadas, profesores certificados y tecnología de vanguardia en nuestras 3 sedes.
            </p>

            <!-- Acciones CTA -->
            <div class="mt-10 flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                @auth
                    <a 
                        href="{{ Auth::user()->esSocio() ? route('mi-perfil') : route('dashboard') }}"
                        class="w-full sm:w-auto bg-lime-500 hover:bg-lime-400 text-zinc-950 font-black text-sm sm:text-base uppercase tracking-wider px-8 py-4 rounded-xl transition-all duration-300 shadow-[0_0_20px_rgba(132,204,22,0.4)] hover:shadow-[0_0_30px_rgba(132,204,22,0.65)] hover:-translate-y-0.5 cursor-pointer flex items-center justify-center gap-3"
                    >
                        <span>IR A MI {{ Auth::user()->esSocio() ? 'PERFIL' : 'PANEL DE CONTROL' }}</span>
                        <svg class="w-5 h-5 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                @else
                    <a 
                        href="{{ route('register') }}"
                        class="w-full sm:w-auto bg-lime-500 hover:bg-lime-400 text-zinc-950 font-black text-sm sm:text-base uppercase tracking-wider px-8 py-4 rounded-xl transition-all duration-300 shadow-[0_0_20px_rgba(132,204,22,0.4)] hover:shadow-[0_0_30px_rgba(132,204,22,0.65)] hover:-translate-y-0.5 cursor-pointer flex items-center justify-center gap-3"
                    >
                        <span>ÚNETE HOY / REGISTRARSE</span>
                        <svg class="w-5 h-5 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                @endauth

                <a 
                    href="#membresias" 
                    class="w-full sm:w-auto bg-white hover:bg-slate-100 dark:bg-zinc-900/90 dark:hover:bg-zinc-800 border border-slate-300 dark:border-zinc-800 hover:border-lime-500/50 text-slate-800 dark:text-white font-bold text-sm sm:text-base uppercase tracking-wider px-7 py-4 rounded-xl transition duration-200 flex items-center justify-center gap-2 shadow-xs"
                >
                    <span>VER PLANES Y PRECIOS</span>
                    <svg class="w-4 h-4 text-lime-600 dark:text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
            </div>

            <!-- Micro KPIs de Confianza -->
            <div class="mt-14 grid grid-cols-2 sm:grid-cols-4 gap-6 pt-10 border-t border-slate-200 dark:border-zinc-800/80 w-full max-w-4xl text-slate-500 dark:text-zinc-400">
                <div class="flex flex-col items-center">
                    <span class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white font-mono">+1,500</span>
                    <span class="text-xs uppercase font-bold tracking-wider mt-1 text-slate-500 dark:text-zinc-400">Socios Registrados</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-3xl sm:text-4xl font-black text-lime-600 dark:text-lime-400 font-mono">3 Sedes</span>
                    <span class="text-xs uppercase font-bold tracking-wider mt-1 text-slate-500 dark:text-zinc-400">Presencia Nacional</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white font-mono">5 Clases</span>
                    <span class="text-xs uppercase font-bold tracking-wider mt-1 text-slate-500 dark:text-zinc-400">Disciplinas Grupales</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-3xl sm:text-4xl font-black text-lime-600 dark:text-lime-400 font-mono">24/7</span>
                    <span class="text-xs uppercase font-bold tracking-wider mt-1 text-slate-500 dark:text-zinc-400">Acceso Inteligente</span>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         4. SECCIÓN NOSOTROS (Filosofía, Trayectoria & Tecnología Fitness)
         ========================================================================= -->
    <section id="nosotros" class="scroll-mt-24 py-24 bg-slate-50 dark:bg-zinc-950 relative overflow-hidden border-b border-slate-200/80 dark:border-zinc-800/80 transition-colors duration-200">
        <!-- Resplandor ambiental de fondo -->
        <div class="absolute top-1/2 -left-48 w-96 h-96 bg-lime-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- Columna Izquierda: Multimedia & Banner de Alto Rendimiento -->
                <div class="lg:col-span-6 relative">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <!-- Marco exterior con resplandor -->
                        <div class="relative rounded-3xl overflow-hidden border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl group">
                            <img 
                                src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=1200&auto=format&fit=crop" 
                                alt="Entrenamiento en FitLife Gym" 
                                class="w-full h-[460px] object-cover object-center group-hover:scale-105 transition-transform duration-700 opacity-90 dark:opacity-80"
                                loading="lazy"
                                decoding="async"
                                width="1200"
                                height="800"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>

                            <!-- Overlay de Información en la Tarjeta -->
                            <div class="absolute bottom-6 left-6 right-6 p-5 rounded-2xl bg-white/95 dark:bg-zinc-900/90 border border-slate-200 dark:border-zinc-700/60 backdrop-blur-md shadow-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-lime-500 text-zinc-950 flex items-center justify-center font-black text-xl shadow-md">
                                        FL
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-950 dark:text-white uppercase tracking-tight">Cultura FitLife Pro</h4>
                                        <p class="text-xs text-slate-600 dark:text-zinc-400">Ciencia del movimiento, disciplina y tecnología aplicada.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flotante con Badge de Acreditación -->
                        <div class="absolute -top-5 -right-4 sm:-right-6 bg-lime-500 text-zinc-950 p-4 rounded-2xl shadow-[0_0_25px_rgba(132,204,22,0.4)] border border-lime-400 font-black text-center select-none hidden sm:block">
                            <span class="block text-2xl font-mono leading-none">10+</span>
                            <span class="text-[10px] uppercase tracking-wider font-bold">Años de Liderazgo</span>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Contenido Editorial y Pilares -->
                <div class="lg:col-span-6 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full border border-lime-500/40 bg-lime-500/10 text-lime-700 dark:text-lime-400 text-xs font-black uppercase tracking-widest font-mono">
                        SOBRE FITLIFE GYM
                    </div>

                    <h2 class="text-3xl sm:text-5xl font-black text-slate-950 dark:text-white uppercase tracking-tight leading-tight">
                        Más que un gimnasio, una <span class="text-lime-600 dark:text-lime-400">comunidad</span> de superación.
                    </h2>

                    <p class="text-sm sm:text-base text-slate-600 dark:text-zinc-300 leading-relaxed">
                        En FitLife combinamos equipamiento biomecánico de última generación, entrenadores certificados y un sistema digital automatizado que elimina fricciones: ingresa en segundos con tu carnet inteligente y gestiona tu progreso deportivo desde cualquier dispositivo.
                    </p>

                    <!-- Pilares Clave -->
                    <div class="grid sm:grid-cols-2 gap-4 pt-4">
                        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/50 transition shadow-xs">
                            <div class="w-8 h-8 rounded-lg bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-950 dark:text-white uppercase">Entrenadores Elite</h3>
                            <p class="text-xs text-slate-600 dark:text-zinc-400 mt-1">Metodologías comprobadas y acompañamiento profesional constante.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/50 transition shadow-xs">
                            <div class="w-8 h-8 rounded-lg bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-950 dark:text-white uppercase">Acceso Inteligente</h3>
                            <p class="text-xs text-slate-600 dark:text-zinc-400 mt-1">Control por carnet digital, código de barras y torniquetes rápidos.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/50 transition shadow-xs">
                            <div class="w-8 h-8 rounded-lg bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-950 dark:text-white uppercase">Equipamiento Pro</h3>
                            <p class="text-xs text-slate-600 dark:text-zinc-400 mt-1">Máquinas de resistencia constante, pesas rusas, barras olímpicas y mancuernas.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/50 transition shadow-xs">
                            <div class="w-8 h-8 rounded-lg bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-950 dark:text-white uppercase">Red 3 Sedes</h3>
                            <p class="text-xs text-slate-600 dark:text-zinc-400 mt-1">Flexibilidad para entrenar en Las Mercedes, Chacao y El Viñedo.</p>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center gap-4">
                        <a 
                            href="{{ route('register') }}"
                            class="bg-lime-500 hover:bg-lime-400 text-zinc-950 font-black text-xs uppercase tracking-wider px-6 py-3 rounded-xl transition shadow-[0_0_15px_rgba(132,204,22,0.35)]"
                        >
                            Comienza tu Transformación
                        </a>
                        <a href="#servicios" class="text-xs font-bold text-slate-700 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 transition underline">
                            Explorar Servicios →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         5. SECCIÓN SERVICIOS (Oferta Integral de Salud y Fitness)
         ========================================================================= -->
    <section id="servicios" class="scroll-mt-24 py-24 bg-white dark:bg-[#090d16] relative border-b border-slate-200/80 dark:border-zinc-800/80 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-lime-700 dark:text-lime-400 text-xs font-black uppercase tracking-widest font-mono">SOLUCIONES FITLIFE DE CLASE MUNDIAL</span>
                <h2 class="text-3xl sm:text-5xl font-black text-slate-950 dark:text-white uppercase tracking-tight mt-1">
                    Nuestros Servicios Especializados
                </h2>
                <p class="text-slate-600 dark:text-zinc-400 text-sm sm:text-base mt-3">
                    Cada área del gimnasio está planificada para maximizar tu salud, fuerza y bienestar físico con tecnología de vanguardia.
                </p>
            </div>

            <!-- Grid de Servicios (6 Tarjetas Especializadas) -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Servicio 1: Sala de Musculación & Peso Libre -->
                <div class="bg-slate-50 dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/60 rounded-3xl p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-[0_0_25px_rgba(132,204,22,0.15)] flex flex-col justify-between group shadow-xs">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6.5 6.5 11 11"/>
                                <path d="m21 21-1-1"/>
                                <path d="m3 3 1 1"/>
                                <path d="m18 22 4-4"/>
                                <path d="m2 6 4-4"/>
                                <path d="m3 10 7-7"/>
                                <path d="m14 21 7-7"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-950 dark:text-white uppercase tracking-tight group-hover:text-lime-600 dark:group-hover:text-lime-400 transition">
                            Musculación y Peso Libre
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed mt-2.5">
                            Racks olímpicos, mancuernas calibradas de 2kg a 50kg, bancos ajustables y máquinas guiadas con tecnología de resistencia adaptativa.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 dark:text-zinc-500">Disponible: Todos los planes</span>
                        <span class="text-lime-600 dark:text-lime-400 font-bold">24/7 en Plan Pro</span>
                    </div>
                </div>

                <!-- Servicio 2: Zona Cardiovascular Inteligente -->
                <div class="bg-slate-50 dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/60 rounded-3xl p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-[0_0_25px_rgba(132,204,22,0.15)] flex flex-col justify-between group shadow-xs">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-950 dark:text-white uppercase tracking-tight group-hover:text-lime-600 dark:group-hover:text-lime-400 transition">
                            Cardio y Resistencia
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed mt-2.5">
                            Caminadoras ergonómicas, elípticas, escaladoras StairMaster y remadoras de agua con conectividad para monitoreo de frecuencia cardíaca.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 dark:text-zinc-500">Sensores biométricos</span>
                        <span class="text-lime-600 dark:text-lime-400 font-bold">Incluido</span>
                    </div>
                </div>

                <!-- Servicio 3: Clases Grupales Certificadas -->
                <div class="bg-slate-50 dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/60 rounded-3xl p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-[0_0_25px_rgba(132,204,22,0.15)] flex flex-col justify-between group shadow-xs">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-950 dark:text-white uppercase tracking-tight group-hover:text-lime-600 dark:group-hover:text-lime-400 transition">
                            Clases Grupales Dinámicas
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed mt-2.5">
                            5 disciplinas de élite: Spinning cardiovascular, Yoga restaurativo, Pilates core, Zumba de alta energía y Circuitos Funcionales HIIT.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 dark:text-zinc-500">Cupos en tiempo real</span>
                        <a href="#clases" class="text-lime-600 dark:text-lime-400 font-bold hover:underline">Ver catálogo →</a>
                    </div>
                </div>

                <!-- Servicio 4: Prescripción de Rutinas Técnicas -->
                <div class="bg-slate-50 dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/60 rounded-3xl p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-[0_0_25px_rgba(132,204,22,0.15)] flex flex-col justify-between group shadow-xs">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-950 dark:text-white uppercase tracking-tight group-hover:text-lime-600 dark:group-hover:text-lime-400 transition">
                            Rutinas y Seguimiento Digital
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed mt-2.5">
                            Programación personalizada por nivel (Principiante, Intermedio, Avanzado) con series, repeticiones y tiempos de descanso en tu perfil móvil.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 dark:text-zinc-500">Diseñado por Coaches</span>
                        <span class="text-lime-600 dark:text-lime-400 font-bold">100% Personalizado</span>
                    </div>
                </div>

                <!-- Servicio 5: Control de Acceso Biométrico & QR -->
                <div class="bg-slate-50 dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/60 rounded-3xl p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-[0_0_25px_rgba(132,204,22,0.15)] flex flex-col justify-between group shadow-xs">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-950 dark:text-white uppercase tracking-tight group-hover:text-lime-600 dark:group-hover:text-lime-400 transition">
                            Carnet Digital y Kioscos QR
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed mt-2.5">
                            Tu credencial vive en tu teléfono. Validación en menos de 200ms con torniquetes automáticos, cálculo de tiempo de permanencia y modo autoservicio.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 dark:text-zinc-500">Check-in / Check-out</span>
                        <span class="text-lime-600 dark:text-lime-400 font-bold">Sin Esperas</span>
                    </div>
                </div>

                <!-- Servicio 6: Áreas de Recuperación & Locker Rooms -->
                <div class="bg-slate-50 dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 hover:border-lime-500/60 rounded-3xl p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-[0_0_25px_rgba(132,204,22,0.15)] flex flex-col justify-between group shadow-xs">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-lime-500/15 border border-lime-500/30 flex items-center justify-center text-lime-600 dark:text-lime-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-950 dark:text-white uppercase tracking-tight group-hover:text-lime-600 dark:group-hover:text-lime-400 transition">
                            Vestuarios y Recuperación
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed mt-2.5">
                            Lockers con clave electrónica, duchas con agua caliente presurizada, secadores y zona de hidratación con agua alcalina filtrada.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 dark:text-zinc-500">Confort Premium</span>
                        <span class="text-lime-600 dark:text-lime-400 font-bold">Todas las Sedes</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         6. SECCIÓN DE SUCURSALES (Presencia en las 3 Sedes)
         ========================================================================= -->
    <section id="sucursales" class="scroll-mt-24 py-20 bg-slate-100/70 dark:bg-zinc-900/60 border-y border-slate-200/80 dark:border-zinc-800/80 relative transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="text-lime-700 dark:text-lime-400 text-xs font-black uppercase tracking-widest font-mono">RED FITLIFE PREMIUM</span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-950 dark:text-white uppercase tracking-tight mt-1">
                    Nuestras 3 Sucursales Oficiales
                </h2>
                <p class="text-slate-600 dark:text-zinc-400 text-sm mt-2">
                    Instalaciones de primer nivel climatizadas, salas de musculación libre, áreas cardiovasculares y studios funcionales.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($sucursales as $sucursal)
                    <div class="bg-white dark:bg-zinc-950/80 border border-slate-200 dark:border-zinc-800/90 hover:border-lime-500/50 rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-[0_0_25px_rgba(132,204,22,0.15)] flex flex-col justify-between group shadow-xs">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded bg-lime-500/10 text-lime-700 dark:text-lime-400 border border-lime-500/20">
                                    SEDE #{{ $sucursal->id }}
                                </span>
                                <span class="flex h-2.5 w-2.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-lime-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-lime-500"></span>
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-slate-950 dark:text-white group-hover:text-lime-600 dark:group-hover:text-lime-400 transition">
                                {{ $sucursal->nombre }}
                            </h3>
                            <p class="mt-2 text-xs text-slate-600 dark:text-zinc-400 leading-relaxed flex items-start gap-2">
                                <svg class="w-4 h-4 text-lime-600 dark:text-lime-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $sucursal->direccion }}</span>
                            </p>
                            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex items-center justify-between text-xs text-slate-500 dark:text-zinc-400">
                                <span>Ciudad: <strong class="text-slate-800 dark:text-white">{{ $sucursal->ciudad }}</strong></span>
                                <span>Estado: <strong class="text-slate-800 dark:text-white">{{ $sucursal->estado }}</strong></span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-200 dark:border-zinc-800/80 flex items-center justify-between">
                            <span class="text-[11px] text-slate-500 dark:text-zinc-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-lime-600 dark:text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Torniquetes QR activos
                            </span>
                            <a 
                                href="{{ route('register') }}"
                                class="text-xs font-bold text-lime-600 dark:text-lime-400 hover:text-lime-700 dark:hover:text-lime-300 underline"
                            >
                                Registrarme aquí →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- =========================================================================
         7. SECCIÓN DINÁMICA DE MEMBRESÍAS (Desde PostgreSQL: $50, $80, $120)
         ========================================================================= -->
    <section id="membresias" class="scroll-mt-24 py-24 bg-white dark:bg-zinc-950 relative overflow-hidden transition-colors duration-200 border-b border-slate-200/80 dark:border-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-lime-700 dark:text-lime-400 text-xs font-black uppercase tracking-widest font-mono">TARIFAS Y BENEFICIOS TRANSPARENTES</span>
                <h2 class="text-3xl sm:text-5xl font-black text-slate-950 dark:text-white uppercase tracking-tight mt-1">
                    Planes de Membresía Oficiales
                </h2>
                <p class="text-slate-600 dark:text-zinc-400 text-base mt-3">
                    Elige el nivel de entrenamiento que mejor se adapte a tu estilo de vida. Sin contratos de permanencia engañosos.
                </p>
            </div>

            <!-- Tarjetas Comparativas de los 3 Planes -->
            <div class="grid lg:grid-cols-3 gap-8 items-stretch">
                @foreach ($planes as $plan)
                    @php
                        $esBasico = $plan->esBasico();
                        $esCompleto = $plan->esCompleto();
                        $esPremium = $plan->esPremium();
                    @endphp
                    <div class="relative bg-slate-50 dark:bg-zinc-900/90 border {{ $esCompleto ? 'border-lime-500 shadow-xl dark:shadow-[0_0_35px_rgba(132,204,22,0.2)] scale-102 lg:-translate-y-2' : 'border-slate-200 dark:border-zinc-800 shadow-xs' }} rounded-3xl p-8 flex flex-col justify-between transition-all duration-300 hover:border-lime-500/80">
                        
                        <!-- Badge Destacado en Plan Completo -->
                        @if ($esCompleto)
                            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-lime-500 text-zinc-950 text-[11px] font-black uppercase tracking-widest px-4 py-1 rounded-full shadow-md">
                                MÁS POPULAR
                            </div>
                        @endif

                        <div>
                            <!-- Encabezado del Plan -->
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-2xl font-black text-slate-950 dark:text-white uppercase tracking-tight">
                                    {{ $plan->nombre }}
                                </h3>
                                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded {{ $esPremium ? 'bg-amber-400/15 text-amber-700 dark:text-amber-400 border border-amber-400/30' : 'bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300' }}">
                                    {{ $plan->duracion_dias }} DÍAS
                                </span>
                            </div>
                            
                            <p class="text-xs text-slate-600 dark:text-zinc-400 min-h-[36px] leading-relaxed">
                                {{ $plan->descripcion }}
                            </p>

                            <!-- Precio Mensual -->
                            <div class="mt-6 mb-6 pb-6 border-b border-slate-200 dark:border-zinc-800">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-bold text-lime-600 dark:text-lime-400">$</span>
                                    <span class="text-5xl font-black text-slate-950 dark:text-white font-mono tracking-tight">
                                        {{ number_format($plan->precio, 0) }}
                                    </span>
                                    <span class="text-sm font-semibold text-slate-500 dark:text-zinc-400">/ mes</span>
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-zinc-400 block mt-1">Facturación mensual en USD o equivalente en Bs.</span>
                            </div>

                            <!-- Lista de Reglas de Negocio Específicas del Plan -->
                            <div class="space-y-3.5 text-xs text-slate-700 dark:text-zinc-300">
                                <!-- Horarios de Acceso -->
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-lime-600 dark:text-lime-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    @if ($esBasico)
                                        <span>Horario restringido: <strong>Lunes a Viernes de 08:00 a 18:00 hrs</strong>.</span>
                                    @else
                                        <span>Acceso <strong>libre e ilimitado 24/7</strong> los 365 días del año.</span>
                                    @endif
                                </div>

                                <!-- Acceso a Sala y Máquinas -->
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-lime-600 dark:text-lime-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Acceso total a <strong>sala de musculación y peso libre</strong>.</span>
                                </div>

                                <!-- Clases Grupales (Regla de Negocio Estricta) -->
                                <div class="flex items-start gap-2.5">
                                    @if ($esBasico)
                                        <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <span class="text-slate-400 dark:text-zinc-400"><strong>Sin acceso a clases grupales</strong> (Spinning, Yoga, Pilates, etc.).</span>
                                    @elseif ($esCompleto)
                                        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span>Clases grupales disponibles con <strong>recargo de $10 por clase</strong>.</span>
                                    @else
                                        <svg class="w-4 h-4 text-lime-600 dark:text-lime-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Clases grupales <strong>100% INCLUIDAS</strong> sin ningún costo adicional.</span>
                                    @endif
                                </div>

                                <!-- Invitados Libres -->
                                <div class="flex items-start gap-2.5">
                                    @if ($esPremium)
                                        <svg class="w-4 h-4 text-lime-600 dark:text-lime-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Pase libre para <strong>invitados libres sin límite</strong>.</span>
                                    @else
                                        <svg class="w-4 h-4 text-slate-400 dark:text-zinc-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <span class="text-slate-400 dark:text-zinc-400">Pase de invitados no incluido.</span>
                                    @endif
                                </div>

                                <!-- Carnet Digital FitLife QR -->
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-lime-600 dark:text-lime-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Carnet digital con <strong>código QR de acceso rápido</strong>.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botón CTA de Selección del Plan -->
                        <div class="mt-8 pt-6 border-t border-slate-200 dark:border-zinc-800">
                            @auth
                                <a 
                                    href="{{ Auth::user()->esSocio() ? route('mi-perfil') : route('dashboard') }}"
                                    class="w-full py-3.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 {{ $esCompleto ? 'bg-lime-500 hover:bg-lime-400 text-zinc-950 shadow-[0_0_20px_rgba(132,204,22,0.4)]' : 'bg-slate-200 hover:bg-slate-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-800 dark:text-white hover:text-lime-700 dark:hover:text-lime-400 border border-slate-300 dark:border-zinc-700' }}"
                                >
                                    <span>IR A MI {{ Auth::user()->esSocio() ? 'PERFIL' : 'DASHBOARD' }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @else
                                <a 
                                    href="{{ route('register', ['plan' => $plan->id]) }}"
                                    class="w-full py-3.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 {{ $esCompleto ? 'bg-lime-500 hover:bg-lime-400 text-zinc-950 shadow-[0_0_20px_rgba(132,204,22,0.4)]' : 'bg-slate-200 hover:bg-slate-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-800 dark:text-white hover:text-lime-700 dark:hover:text-lime-400 border border-slate-300 dark:border-zinc-700' }}"
                                >
                                    <span>SUSCRIBIRME AL {{ $plan->nombre }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- =========================================================================
         8. CATÁLOGO DE CLASES GRUPALES DESTACADAS (Grid Dinámico desde DB)
         ========================================================================= -->
    <section id="clases" class="scroll-mt-24 py-24 bg-slate-50 dark:bg-zinc-900/40 border-t border-slate-200/80 dark:border-zinc-800/80 relative transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-14">
                <div>
                    <span class="text-lime-700 dark:text-lime-400 text-xs font-black uppercase tracking-widest font-mono">DISCIPLINAS Y ENTRENAMIENTO EN GRUPO</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-950 dark:text-white uppercase tracking-tight mt-1">
                        Catálogo de Clases Grupales
                    </h2>
                    <p class="text-slate-600 dark:text-zinc-400 text-sm mt-1">
                        Spinning, Yoga, Pilates, Zumba y Funcional impartidas por instructores profesionales.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 dark:text-zinc-400">Total clases activas:</span>
                    <span class="px-2.5 py-1 rounded bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-mono font-bold text-lime-600 dark:text-lime-400 text-xs shadow-xs">
                        {{ $clasesGrupales->count() }} Disciplinas
                    </span>
                </div>
            </div>

            <!-- Grid de Clases Grupales -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($clasesGrupales as $clase)
                    <div class="bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800/90 hover:border-lime-500/60 rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-[0_0_25px_rgba(132,204,22,0.15)] flex flex-col justify-between shadow-xs">
                        <div>
                            <!-- Header de la Clase -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-lime-500/10 text-lime-700 dark:text-lime-400 border border-lime-500/20">
                                    {{ $clase->nombre }}
                                </span>
                                <span class="text-[11px] font-mono text-slate-500 dark:text-zinc-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-lime-600 dark:text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $clase->duracion_minutos }} min
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-950 dark:text-white">{{ $clase->nombre }}</h3>
                            <p class="mt-2 text-xs text-slate-600 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                                {{ $clase->descripcion }}
                            </p>

                            <!-- Datos Clave: Profesor, Horario, Cupo y Sede -->
                            <div class="mt-5 space-y-2 text-xs border-t border-slate-100 dark:border-zinc-900 pt-4">
                                <!-- Profesor Asignado -->
                                <div class="flex items-center justify-between text-slate-700 dark:text-zinc-300">
                                    <span class="text-slate-500 dark:text-zinc-500">Instructor:</span>
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        {{ $clase->profesor ? $clase->profesor->nombre . ' ' . $clase->profesor->apellido : 'Instructor Certificado' }}
                                    </span>
                                </div>

                                <!-- Horario / Días -->
                                <div class="flex items-center justify-between text-slate-700 dark:text-zinc-300">
                                    <span class="text-slate-500 dark:text-zinc-500">Horario:</span>
                                    <span class="font-mono font-semibold text-lime-600 dark:text-lime-400">
                                        {{ $clase->horario ?? ($clase->dia_semana . ' ' . $clase->hora_inicio) }}
                                    </span>
                                </div>

                                <!-- Cupo Máximo -->
                                <div class="flex items-center justify-between text-slate-700 dark:text-zinc-300">
                                    <span class="text-slate-500 dark:text-zinc-500">Cupo Máximo:</span>
                                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $clase->cupo_maximo }} alumnos</span>
                                </div>

                                <!-- Sede / Sala -->
                                <div class="flex items-center justify-between text-slate-700 dark:text-zinc-300">
                                    <span class="text-slate-500 dark:text-zinc-500">Sede:</span>
                                    <span class="font-semibold text-slate-800 dark:text-zinc-300 truncate max-w-[160px]">
                                        {{ $clase->sucursal?->nombre ?? 'Sede Principal' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer con Badge de Plan -->
                        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-zinc-900 flex items-center justify-between">
                            <span class="text-[11px] text-slate-500 dark:text-zinc-400">
                                Plan Premium: <strong class="text-lime-600 dark:text-lime-400">Gratis</strong>
                            </span>
                            @auth
                                <a 
                                    href="{{ Auth::user()->esSocio() ? route('mi-perfil') : route('clases.index') }}"
                                    class="text-xs font-bold text-lime-600 dark:text-lime-400 hover:text-lime-700 dark:hover:text-lime-300 underline"
                                >
                                    {{ Auth::user()->esSocio() ? 'Ver Horario →' : 'Gestionar Clases →' }}
                                </a>
                            @else
                                <a 
                                    href="{{ route('register') }}"
                                    class="text-xs font-bold text-lime-600 dark:text-lime-400 hover:text-lime-700 dark:hover:text-lime-300 underline"
                                >
                                    Inscribirme →
                                </a>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 text-slate-500 dark:text-zinc-400">
                        No hay clases grupales registradas en este momento.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- =========================================================================
         9. FOOTER INSTITUCIONAL (Diseño Elegante que Corona Ambos Temas)
         ========================================================================= -->
    <footer id="contacto" class="scroll-mt-24 bg-slate-100 dark:bg-slate-950 border-t border-slate-200 dark:border-zinc-800/80 text-slate-600 dark:text-zinc-400 py-16 text-xs transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-8">
            <div class="grid md:grid-cols-4 gap-10 mb-12">
                <!-- Columna de Marca -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-lime-500 flex items-center justify-center text-zinc-950 font-black shadow-xs">
                            FL
                        </div>
                        <span class="text-xl font-black text-slate-950 dark:text-white">Fit<span class="text-lime-600 dark:text-lime-400">Life</span></span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed">
                        Plataforma de alto rendimiento para la gestión integral de gimnasios, credenciales biométricas inteligentes y planes deportivos personalizados.
                    </p>
                    <div class="text-[11px] text-slate-500 dark:text-zinc-500 font-mono">
                        Tecnología respaldada por Laravel 12 & PostgreSQL.
                    </div>
                </div>

                <!-- Enlaces Rápidos -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-950 dark:text-white mb-4">Navegación</h4>
                    <ul class="space-y-2">
                        <li><a href="#inicio" class="text-slate-600 dark:text-zinc-400 hover:text-lime-600 dark:hover:text-lime-400 transition">Inicio</a></li>
                        <li><a href="#nosotros" class="text-slate-600 dark:text-zinc-400 hover:text-lime-600 dark:hover:text-lime-400 transition">Sobre Nosotros</a></li>
                        <li><a href="#servicios" class="text-slate-600 dark:text-zinc-400 hover:text-lime-600 dark:hover:text-lime-400 transition">Nuestros Servicios</a></li>
                        <li><a href="#sucursales" class="text-slate-600 dark:text-zinc-400 hover:text-lime-600 dark:hover:text-lime-400 transition">Nuestras 3 Sucursales</a></li>
                        <li><a href="#membresias" class="text-slate-600 dark:text-zinc-400 hover:text-lime-600 dark:hover:text-lime-400 transition">Planes y Precios</a></li>
                        <li><a href="#clases" class="text-slate-600 dark:text-zinc-400 hover:text-lime-600 dark:hover:text-lime-400 transition">Clases Grupales</a></li>
                        <li>
                            <a href="{{ route('login') }}" class="text-slate-600 dark:text-zinc-400 hover:text-lime-600 dark:hover:text-lime-400 transition text-left">
                                Acceso al Sistema
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Sucursales Físicas -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-950 dark:text-white mb-4">Sedes Físicas</h4>
                    <ul class="space-y-2.5 text-slate-600 dark:text-zinc-400 text-[11px]">
                        <li>
                            <strong class="text-slate-900 dark:text-zinc-200 block">Sede Las Mercedes (Caracas):</strong>
                            Av. Principal de Las Mercedes, Torre Financiera, Piso 1.
                        </li>
                        <li>
                            <strong class="text-slate-900 dark:text-zinc-200 block">Sede Chacao (Caracas):</strong>
                            Edif. Centro Plaza, Nivel Jardín.
                        </li>
                        <li>
                            <strong class="text-slate-900 dark:text-zinc-200 block">Sede El Viñedo (Valencia):</strong>
                            Av. Monseñor Adams, C.C. El Viñedo, Nivel C1.
                        </li>
                    </ul>
                </div>

                <!-- Soporte y Contacto -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-950 dark:text-white mb-4">Atención al Socio</h4>
                    <ul class="space-y-2 text-[11px]">
                        <li>Teléfono Central: <a href="tel:+582129991122" class="text-slate-900 dark:text-white hover:text-lime-600 dark:hover:text-lime-400 font-mono font-medium">+58 212 999 1122</a></li>
                        <li>WhatsApp Soporte: <a href="tel:+584121234567" class="text-slate-900 dark:text-white hover:text-lime-600 dark:hover:text-lime-400 font-mono font-medium">+58 412 123 4567</a></li>
                        <li>Email: <a href="mailto:soporte@fitlife.test" class="text-slate-900 dark:text-white hover:text-lime-600 dark:hover:text-lime-400 font-medium">soporte@fitlife.test</a></li>
                        <li class="pt-2 text-slate-500 dark:text-zinc-400">
                            Acceso seguro con cifrado SSL 256-Bit y código QR dinámico.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright Inferior -->
            <div class="pt-8 border-t border-slate-200 dark:border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500 dark:text-zinc-400">
                <span>© {{ date('Y') }} FitLife Gym Technologies C.A. Todos los derechos reservados.</span>
                <div class="flex items-center gap-6">
                    <a href="#contacto" class="hover:text-slate-900 dark:hover:text-zinc-300 transition">Términos del Servicio</a>
                    <a href="#contacto" class="hover:text-slate-900 dark:hover:text-zinc-300 transition">Políticas de Privacidad</a>
                    <a href="#contacto" class="hover:text-slate-900 dark:hover:text-zinc-300 transition">Reglamento Interno</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Script de Navegación Suave, Centrado Automático y ScrollSpy -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stickyNav = document.querySelector('nav.sticky') || document.querySelector('nav');
            const navLinksDesktop = document.querySelectorAll('.nav-link');
            const navLinksMobile = document.querySelectorAll('.mobile-nav-link');

            function actualizarLinkActivo(idActivo) {
                if (!idActivo) return;

                navLinksDesktop.forEach(function(link) {
                    const match = link.getAttribute('data-target') === idActivo;
                    link.classList.toggle('text-lime-600', match);
                    link.classList.toggle('dark:text-lime-400', match);
                    link.classList.toggle('text-slate-600', !match);
                    link.classList.toggle('dark:text-zinc-300', !match);
                });

                navLinksMobile.forEach(function(link) {
                    const match = link.getAttribute('data-target') === idActivo;
                    link.classList.toggle('text-lime-600', match);
                    link.classList.toggle('dark:text-lime-400', match);
                    link.classList.toggle('text-slate-700', !match);
                    link.classList.toggle('dark:text-zinc-300', !match);
                });
            }

            function centrarEnPantalla(targetElement) {
                if (!targetElement) return;

                // Contenedor principal donde reside la información visible de la sección
                const contenido = targetElement.querySelector('.max-w-7xl, .max-w-6xl') || targetElement;

                const stickyNav = document.querySelector('nav.sticky') || document.querySelector('nav');
                const navHeight = stickyNav ? stickyNav.offsetHeight : 80;
                const windowHeight = window.innerHeight;
                
                // Medir posición y altura del bloque de información real
                const rect = contenido.getBoundingClientRect();
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                const contentTop = rect.top + currentScroll;
                const contentHeight = contenido.offsetHeight;
                
                // Altura libre disponible debajo de la barra de navegación pegajosa
                const visibleHeight = windowHeight - navHeight;

                let scrollYTarget;
                if (contentHeight <= visibleHeight) {
                    // La información cabe en la ventana: se centra simétricamente de forma vertical
                    const space = (visibleHeight - contentHeight) / 2;
                    scrollYTarget = contentTop - navHeight - space;
                } else {
                    // Si la información supera la altura de la ventana (ej. pantallas pequeñas), alinear arriba
                    scrollYTarget = contentTop - navHeight - 16;
                }

                window.scrollTo({
                    top: Math.max(0, Math.round(scrollYTarget)),
                    behavior: 'smooth'
                });

                if (targetElement.id) {
                    actualizarLinkActivo(targetElement.id);
                }
            }

            // ScrollSpy mediante IntersectionObserver
            const secciones = ['inicio', 'nosotros', 'servicios', 'clases', 'sucursales', 'membresias', 'contacto'];
            const elementosSecciones = secciones.map(id => document.getElementById(id)).filter(Boolean);

            if ('IntersectionObserver' in window && elementosSecciones.length > 0) {
                const observerOpciones = {
                    root: null,
                    rootMargin: '-25% 0px -45% 0px',
                    threshold: 0
                };

                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            actualizarLinkActivo(entry.target.id);
                        }
                    });
                }, observerOpciones);

                elementosSecciones.forEach(function(sec) {
                    observer.observe(sec);
                });
            }

            // Delegar clics en todos los enlaces ancla locales (#...)
            document.querySelectorAll('a[href^="#"]').forEach(function(enlace) {
                enlace.addEventListener('click', function(e) {
                    const hash = this.getAttribute('href');
                    if (!hash || hash === '#') return;

                    const destino = document.querySelector(hash);
                    if (destino) {
                        e.preventDefault();

                        if (hash === '#inicio') {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            actualizarLinkActivo('inicio');
                            history.pushState(null, null, hash);
                            return;
                        }

                        // Ejecutar centrado suave inmediato
                        centrarEnPantalla(destino);

                        // Si es pantalla móvil, reajustar tras cerrar el menú
                        if (window.innerWidth < 1024) {
                            setTimeout(function() {
                                centrarEnPantalla(destino);
                            }, 120);
                        }

                        history.pushState(null, null, hash);
                    }
                });
            });

            // Si se accede directamente con un hash en la URL (#sucursales, etc.)
            if (window.location.hash) {
                const destinoInicial = document.querySelector(window.location.hash);
                if (destinoInicial) {
                    setTimeout(function() {
                        centrarEnPantalla(destinoInicial);
                    }, 200);
                }
            }
        });
    </script>
</body>
</html>

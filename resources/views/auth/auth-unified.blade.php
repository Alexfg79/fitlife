<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife OS • Acceso y Registro</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <!-- Tailwind CSS v3 / CDN para uso directo -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        lime: {
                            400: '#a3e635',
                            500: '#84cc16',
                        },
                    },
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <!-- Alpine.js v3 -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen w-full bg-slate-950 text-slate-100 font-sans selection:bg-lime-500 selection:text-slate-950 antialiased"
      x-data="{
          tab: '{{ old('dni') ? 'register' : ($initialTab ?? 'login') }}',
          showPassLogin: false,
          showPassReg: false,
          showPassConf: false,
          regPassword: '',
          submitting: false,
          get strength() {
              const pass = this.regPassword;
              if (!pass) return { score: 0, label: '', color: 'bg-slate-800' };
              let pts = 0;
              if (pass.length >= 8) pts++;
              if (/[A-Z]/.test(pass)) pts++;
              if (/[0-9]/.test(pass)) pts++;
              if (/[^A-Za-z0-9]/.test(pass)) pts++;

              if (pts <= 1) return { score: 1, label: 'Débil', color: 'bg-rose-500' };
              if (pts <= 3) return { score: 2, label: 'Media', color: 'bg-amber-400' };
              return { score: 3, label: 'Fuerte', color: 'bg-lime-400' };
          }
      }">

    <div class="grid min-h-screen lg:grid-cols-12">
        <!-- =========================================================================
            PANEL IZQUIERDO: SPLIT-SCREEN MOTIVACIONAL (Desktop >= 1024px)
            ========================================================================= -->
        <div class="relative hidden lg:col-span-6 xl:col-span-6 lg:flex flex-col justify-between overflow-hidden p-12 xl:p-16 border-r border-slate-800/80">
            <!-- Imagen Deportiva HD -->
            <div class="absolute inset-0 bg-cover bg-center scale-105"
                 style="background-image: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1920&auto=format&fit=crop');">
            </div>
            <!-- Gradiente Cinematográfico Oscuro -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-slate-950/60 backdrop-blur-[2px]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-lime-500/20 via-transparent to-transparent pointer-events-none"></div>

            <!-- Logo Superior -->
            <div class="relative z-20 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-400 to-emerald-500 shadow-[0_0_30px_rgba(132,204,22,0.4)]">
                        <svg class="h-6 w-6 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-2xl font-black tracking-tight text-white uppercase italic">
                            FITLIFE <span class="text-lime-400 font-extrabold not-italic">OS</span>
                        </span>
                        <span class="block text-[10px] font-bold tracking-widest uppercase text-slate-400">
                            Gym Management Pro
                        </span>
                    </div>
                </div>

                <span class="rounded-full border border-lime-500/30 bg-lime-500/10 px-3 py-1 text-xs font-bold text-lime-400">
                    SISTEMA ACTIVO
                </span>
            </div>

            <!-- Cita Central -->
            <div class="relative z-20 my-auto max-w-xl py-10">
                <span class="mb-4 inline-block rounded-lg border border-slate-700/80 bg-slate-900/80 px-3 py-1 text-xs font-semibold text-slate-300">
                    ⚡ Rendimiento & Disciplina
                </span>
                <blockquote class="space-y-4">
                    <h2 class="text-3xl xl:text-5xl font-black tracking-tight text-white leading-tight">
                        Construye tu mejor versión con <span class="text-transparent bg-clip-text bg-gradient-to-r from-lime-400 to-emerald-400">tecnología deportiva</span>.
                    </h2>
                    <p class="text-base text-slate-300">
                        Credencial digital con QR, rutinas técnicas individualizadas y control de acceso biométrico en tiempo real.
                    </p>
                </blockquote>
            </div>

            <!-- Footer Izquierdo -->
            <div class="relative z-20 flex items-center justify-between text-xs text-slate-500">
                <span>© {{ date('Y') }} FitLife Technologies Inc.</span>
                <span>Seguridad SSL 256-Bit</span>
            </div>
        </div>

        <!-- =========================================================================
            PANEL DERECHO: FORMULARIO UNIFICADO CON TABS EN ALPINE.JS
            ========================================================================= -->
        <div class="lg:col-span-6 xl:col-span-6 flex flex-col justify-center px-4 sm:px-10 xl:px-16 py-8 md:py-12 bg-slate-950 relative overflow-y-auto">
            <div class="mx-auto w-full max-w-lg">
                <!-- Conmutador de Tabs (Pestañas) -->
                <div class="mb-6 rounded-2xl border border-slate-800/90 bg-slate-900/90 p-1.5 shadow-lg">
                    <div class="grid grid-cols-2 gap-1">
                        <button type="button"
                                @click="tab = 'login'"
                                :class="tab === 'login'
                                    ? 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.3)]'
                                    : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                                class="flex items-center justify-center gap-2 rounded-xl py-2.5 text-xs sm:text-sm font-black uppercase tracking-wider transition-all duration-200">
                            Iniciar Sesión
                        </button>

                        <button type="button"
                                @click="tab = 'register'"
                                :class="tab === 'register'
                                    ? 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.3)]'
                                    : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                                class="flex items-center justify-center gap-2 rounded-xl py-2.5 text-xs sm:text-sm font-black uppercase tracking-wider transition-all duration-200">
                            Crear Cuenta
                        </button>
                    </div>
                </div>

                <!-- ==========================================
                    PESTAÑA 1: LOGIN (INICIAR SESIÓN)
                    ========================================== -->
                <div x-show="tab === 'login'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                    <div class="mb-6">
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            Iniciar Sesión
                        </h1>
                        <p class="mt-1 text-sm text-slate-400">
                            Ingresa tus credenciales autorizadas para acceder al sistema.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" @submit="submitting = true" class="space-y-4">
                        @csrf

                        <!-- Correo Electrónico -->
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Correo Electrónico
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                   placeholder="socio@fitlife.test"
                                   class="w-full min-h-[44px] rounded-xl border @error('email') border-rose-500 ring-1 ring-rose-500 @else border-slate-800 @enderror bg-slate-900/90 py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden" />
                            @error('email')
                                <span class="mt-1 block text-xs font-medium text-rose-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Contraseña con Mostrar/Ocultar -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                    Contraseña
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-lime-400 hover:text-lime-300 transition">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>
                            <div class="relative">
                                <input id="password" :type="showPassLogin ? 'text' : 'password'" name="password" required
                                       placeholder="••••••••••••"
                                       class="w-full min-h-[44px] rounded-xl border @error('password') border-rose-500 @else border-slate-800 @enderror bg-slate-900/90 py-2.5 pl-4 pr-11 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden" />
                                <button type="button" @click="showPassLogin = !showPassLogin"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-white transition">
                                    <span x-text="showPassLogin ? '🙈' : '👁️'"></span>
                                </button>
                            </div>
                            @error('password')
                                <span class="mt-1 block text-xs font-medium text-rose-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Recordar Sesión -->
                        <div class="flex items-center">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded-sm border-slate-700 bg-slate-900 text-lime-500 focus:ring-lime-400" />
                                <span class="text-xs font-medium text-slate-400">Recordar sesión</span>
                            </label>
                        </div>

                        <!-- Botón Submit -->
                        <button type="submit" :disabled="submitting"
                                class="w-full min-h-[48px] rounded-xl bg-gradient-to-r from-lime-400 to-emerald-500 px-4 py-3 text-sm font-black uppercase tracking-wider text-slate-950 shadow-[0_0_25px_rgba(132,204,22,0.35)] hover:shadow-[0_0_35px_rgba(132,204,22,0.55)] transition cursor-pointer disabled:opacity-50">
                            <span x-show="!submitting">Ingresar al Sistema →</span>
                            <span x-show="submitting" x-cloak>Autenticando...</span>
                        </button>
                    </form>
                </div>

                <!-- ==========================================
                    PESTAÑA 2: REGISTER (CREAR CUENTA)
                    ========================================== -->
                <div x-show="tab === 'register'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
                    <div class="mb-6">
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            Crear Cuenta de Socio
                        </h1>
                        <p class="mt-1 text-sm text-slate-400">
                            Regístrate para obtener tu credencial digital FitLife y activar tu membresía.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" @submit="submitting = true" class="space-y-4">
                        @csrf

                        <!-- Nombre y Cédula/DNI -->
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                    Nombre Completo *
                                </label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                                       placeholder="Carlos Mendoza"
                                       class="w-full min-h-[44px] rounded-xl border @error('name') border-rose-500 @else border-slate-800 @enderror bg-slate-900/90 py-2 px-3 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden" />
                                @error('name')
                                    <span class="mt-1 block text-xs font-medium text-rose-400">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="dni" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                    Cédula / DNI *
                                </label>
                                <input id="dni" type="text" name="dni" value="{{ old('dni') }}" required
                                       placeholder="V-28491023"
                                       class="w-full min-h-[44px] rounded-xl border @error('dni') border-rose-500 @else border-slate-800 @enderror bg-slate-900/90 py-2 px-3 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden" />
                                @error('dni')
                                    <span class="mt-1 block text-xs font-medium text-rose-400">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Correo y Teléfono -->
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="reg_email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                    Correo Electrónico *
                                </label>
                                <input id="reg_email" type="email" name="email" value="{{ old('email') }}" required
                                       placeholder="carlos@ejemplo.com"
                                       class="w-full min-h-[44px] rounded-xl border @error('email') border-rose-500 @else border-slate-800 @enderror bg-slate-900/90 py-2 px-3 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden" />
                                @error('email')
                                    <span class="mt-1 block text-xs font-medium text-rose-400">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="telefono" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                    Teléfono Móvil
                                </label>
                                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}"
                                       placeholder="+58 412 1234567"
                                       class="w-full min-h-[44px] rounded-xl border border-slate-800 bg-slate-900/90 py-2 px-3 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden" />
                                @error('telefono')
                                    <span class="mt-1 block text-xs font-medium text-rose-400">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Selector de Plan de Interés -->
                        <div>
                            <label for="plan_id" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Plan Inicial de Interés (Opcional)
                            </label>
                            <select id="plan_id" name="plan_id"
                                    class="w-full min-h-[44px] rounded-xl border border-slate-800 bg-slate-900/90 py-2 px-3 text-sm text-white focus:border-lime-400 focus:outline-hidden">
                                <option value="">Selecciona un plan de membresía...</option>
                                <option value="1">Plan Mensual Básico — $30 USD (30 días)</option>
                                <option value="2">Plan Trimestral Promocional — $75 USD (90 días)</option>
                                <option value="3">Pase Libre Anual — $280 USD (365 días)</option>
                            </select>
                        </div>

                        <!-- Contraseña con Medidor de Fuerza Reactivo -->
                        <div>
                            <label for="reg_password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Contraseña *
                            </label>
                            <div class="relative">
                                <input id="reg_password" :type="showPassReg ? 'text' : 'password'" name="password" required
                                       x-model="regPassword"
                                       placeholder="Mínimo 8 caracteres"
                                       class="w-full min-h-[44px] rounded-xl border @error('password') border-rose-500 @else border-slate-800 @enderror bg-slate-900/90 py-2 pl-3 pr-11 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden" />
                                <button type="button" @click="showPassReg = !showPassReg"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-white transition">
                                    <span x-text="showPassReg ? '🙈' : '👁️'"></span>
                                </button>
                            </div>

                            <!-- Barra visual de fuerza en Alpine.js -->
                            <div x-show="regPassword.length > 0" class="mt-2 space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400">Fortaleza:</span>
                                    <span class="font-bold" :class="{
                                        'text-rose-400': strength.score === 1,
                                        'text-amber-400': strength.score === 2,
                                        'text-lime-400': strength.score === 3
                                    }" x-text="strength.label"></span>
                                </div>
                                <div class="grid grid-cols-3 gap-1.5 h-1.5">
                                    <div class="rounded-full transition-colors" :class="strength.score >= 1 ? strength.color : 'bg-slate-800'"></div>
                                    <div class="rounded-full transition-colors" :class="strength.score >= 2 ? strength.color : 'bg-slate-800'"></div>
                                    <div class="rounded-full transition-colors" :class="strength.score >= 3 ? strength.color : 'bg-slate-800'"></div>
                                </div>
                            </div>
                            @error('password')
                                <span class="mt-1 block text-xs font-medium text-rose-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Confirmación de Contraseña -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Confirmar Contraseña *
                            </label>
                            <div class="relative">
                                <input id="password_confirmation" :type="showPassConf ? 'text' : 'password'" name="password_confirmation" required
                                       placeholder="Repite la contraseña"
                                       class="w-full min-h-[44px] rounded-xl border border-slate-800 bg-slate-900/90 py-2 pl-3 pr-11 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden" />
                                <button type="button" @click="showPassConf = !showPassConf"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-white transition">
                                    <span x-text="showPassConf ? '🙈' : '👁️'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Términos y Condiciones -->
                        <div>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="terminos" value="1" required class="mt-0.5 h-4 w-4 rounded-sm border-slate-700 bg-slate-900 text-lime-500 focus:ring-lime-400" />
                                <span class="text-xs text-slate-400 leading-snug">
                                    Acepto los <a href="#" class="text-lime-400 underline font-medium">Términos de Servicio y Reglamento</a> de FitLife Gym.
                                </span>
                            </label>
                            @error('terminos')
                                <span class="mt-1 block text-xs font-medium text-rose-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Botón Submit Registro -->
                        <button type="submit" :disabled="submitting"
                                class="w-full min-h-[48px] rounded-xl bg-gradient-to-r from-lime-400 to-emerald-500 px-4 py-3 text-sm font-black uppercase tracking-wider text-slate-950 shadow-[0_0_25px_rgba(132,204,22,0.35)] hover:shadow-[0_0_35px_rgba(132,204,22,0.55)] transition cursor-pointer disabled:opacity-50">
                            <span x-show="!submitting">Completar Registro de Socio →</span>
                            <span x-show="submitting" x-cloak>Registrando Socio...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


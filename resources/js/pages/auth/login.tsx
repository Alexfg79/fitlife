import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    ArrowRight,
    Check,
    CheckCircle2,
    Dumbbell,
    Eye,
    EyeOff,
    Flame,
    IdCard,
    KeyRound,
    LoaderCircle,
    Lock,
    Mail,
    Phone,
    Shield,
    ShieldCheck,
    Sparkles,
    User,
    UserCheck,
    UserPlus,
    Users,
    Zap,
} from 'lucide-react';
import React, { FormEventHandler, useState } from 'react';

interface LoginForm {
    [key: string]: any;
    email: string;
    password: string;
    remember: boolean;
}

interface RegisterForm {
    [key: string]: any;
    name: string;
    dni: string;
    email: string;
    telefono: string;
    password: string;
    password_confirmation: string;
    plan_id: string;
    terminos: boolean;
}

interface PlanItem {
    id: number;
    nombre: string;
    precio: number | string;
    duracion_dias: number;
    tipo?: string;
}

interface AuthPageProps {
    status?: string;
    canResetPassword: boolean;
    planes?: PlanItem[];
    initialTab?: 'login' | 'register';
    planSeleccionado?: string | number;
}

export default function Login({
    status,
    canResetPassword = true,
    planes = [],
    initialTab = 'login',
    planSeleccionado,
}: AuthPageProps) {
    const [pestañaActiva, setPestañaActiva] = useState<'login' | 'register'>(initialTab);

    // Estados de visibilidad de contraseña
    const [mostrarContrasenaLogin, setMostrarContrasenaLogin] = useState(false);
    const [mostrarContrasenaReg, setMostrarContrasenaReg] = useState(false);
    const [mostrarContrasenaConf, setMostrarContrasenaConf] = useState(false);

    // Estado para logins rápidos de demo
    const [rolEnProceso, setRolEnProceso] = useState<string | null>(null);

    // Formulario de Inicio de Sesión
    const formLogin = useForm<LoginForm>({
        email: '',
        password: '',
        remember: true,
    });

    // Formulario de Registro de Socio
    const formRegistro = useForm<RegisterForm>({
        name: '',
        dni: '',
        email: '',
        telefono: '',
        password: '',
        password_confirmation: '',
        plan_id: planSeleccionado ? String(planSeleccionado) : (planes[0]?.id ? String(planes[0].id) : ''),
        terminos: false,
    });

    // Cálculo de fortaleza de contraseña en tiempo real
    const calcularFortaleza = (pass: string) => {
        if (!pass) return { score: 0, label: '', color: 'bg-slate-800' };
        let puntos = 0;
        if (pass.length >= 8) puntos += 1;
        if (/[A-Z]/.test(pass)) puntos += 1;
        if (/[0-9]/.test(pass)) puntos += 1;
        if (/[^A-Za-z0-9]/.test(pass)) puntos += 1;

        if (puntos <= 1) return { score: 1, label: 'Débil', color: 'bg-rose-500' };
        if (puntos === 2 || puntos === 3) return { score: 2, label: 'Media', color: 'bg-amber-400' };
        return { score: 3, label: 'Fuerte', color: 'bg-lime-400' };
    };

    const fortaleza = calcularFortaleza(formRegistro.data.password);

    // Enviar Login
    const submitLogin: FormEventHandler = (e) => {
        e.preventDefault();
        formLogin.post(route('login'), {
            onFinish: () => formLogin.reset('password'),
        });
    };

    // Enviar Registro
    const submitRegistro: FormEventHandler = (e) => {
        e.preventDefault();
        formRegistro.post(route('register'), {
            onFinish: () => formRegistro.reset('password', 'password_confirmation'),
        });
    };

    // Login Rápido de Demostración
    const loginRapidoPorRol = (email: string, rolClave: string) => {
        setRolEnProceso(rolClave);
        formLogin.setData({
            email,
            password: 'password',
            remember: true,
        });

        router.post(
            route('login'),
            {
                email,
                password: 'password',
                remember: true,
            },
            {
                onFinish: () => setRolEnProceso(null),
                onError: () => setRolEnProceso(null),
            }
        );
    };

    return (
        <div className="min-h-screen w-full bg-slate-950 text-slate-100 selection:bg-lime-500 selection:text-slate-950 font-sans">
            <Head title={pestañaActiva === 'login' ? 'Iniciar Sesión • FitLife OS' : 'Crear Cuenta de Socio • FitLife OS'} />

            <div className="grid min-h-screen lg:grid-cols-12">
                {/* =========================================================================
                    COLUMNA IZQUIERDA: SPLIT-SCREEN MOTIVACIONAL / BRANDING DE ALTO RENDIMIENTO (Desktop)
                    ========================================================================= */}
                <div className="relative hidden lg:col-span-6 xl:col-span-6 lg:flex flex-col justify-between overflow-hidden p-12 xl:p-16 border-r border-slate-800/80">
                    {/* Imagen deportiva de alta resolución con optimización */}
                    <div
                        className="absolute inset-0 bg-cover bg-center transition-transform duration-1000 scale-105"
                        style={{
                            backgroundImage: `url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1920&auto=format&fit=crop')`,
                        }}
                    />

                    {/* Sobrecapa cinematográfica oscura y gradiente neón */}
                    <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-slate-950/60 backdrop-blur-[2px]" />
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-lime-500/15 via-transparent to-transparent pointer-events-none" />

                    {/* Cabecera de marca */}
                    <div className="relative z-20 flex items-center justify-between">
                        <a href="/" className="group flex items-center gap-3.5 focus:outline-hidden">
                            <div className="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-400 to-emerald-500 shadow-[0_0_30px_rgba(132,204,22,0.45)] transition-transform duration-300 group-hover:scale-105">
                                <Dumbbell className="h-6 w-6 text-slate-950" />
                                <span className="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-lime-400 opacity-75"></span>
                                    <span className="relative inline-flex h-3.5 w-3.5 rounded-full border-2 border-slate-950 bg-lime-400"></span>
                                </span>
                            </div>
                            <div>
                                <span className="text-2xl font-black tracking-tight text-white uppercase italic">
                                    FITLIFE <span className="text-lime-400 font-extrabold not-italic">OS</span>
                                </span>
                                <span className="block text-[10px] font-bold tracking-widest uppercase text-slate-400">
                                    SaaS Elite Gym Management
                                </span>
                            </div>
                        </a>

                        <div className="flex items-center gap-2 rounded-full border border-lime-500/30 bg-lime-500/10 px-3 py-1 text-xs font-bold text-lime-400 backdrop-blur-md">
                            <Flame className="h-3.5 w-3.5 text-lime-400 animate-pulse" />
                            <span>ACCESO Y REGISTRO</span>
                        </div>
                    </div>

                    {/* Cita de poder y métricas de confianza */}
                    <div className="relative z-20 my-auto max-w-xl py-10">
                        <div className="mb-4 inline-flex items-center gap-2 rounded-lg border border-slate-700/80 bg-slate-900/80 px-3 py-1 text-xs font-semibold text-slate-300 backdrop-blur-md">
                            <Sparkles className="h-3.5 w-3.5 text-lime-400" />
                            <span>Gimnasio Conectado • Alta Gama</span>
                        </div>

                        <blockquote className="space-y-4">
                            <h2 className="text-3xl xl:text-5xl font-black tracking-tight text-white leading-tight">
                                Lleva tu rendimiento al siguiente <span className="text-transparent bg-clip-text bg-gradient-to-r from-lime-400 to-emerald-400">nivel profesional</span>.
                            </h2>
                            <p className="text-base xl:text-lg text-slate-300 font-normal leading-relaxed">
                                Credenciales digitales con código QR, rutinas técnicas adaptadas a tus metas y control automático de acceso biométrico.
                            </p>
                        </blockquote>

                        <div className="mt-8 grid grid-cols-3 gap-4 pt-6 border-t border-slate-800/80">
                            <div>
                                <div className="text-2xl font-black text-white font-mono">+1,500</div>
                                <div className="text-xs text-slate-400">Atletas activos</div>
                            </div>
                            <div>
                                <div className="text-2xl font-black text-lime-400 font-mono">99.9%</div>
                                <div className="text-xs text-slate-400">Uptime torniquetes</div>
                            </div>
                            <div>
                                <div className="text-2xl font-black text-emerald-400 font-mono">&lt; 0.2s</div>
                                <div className="text-xs text-slate-400">Validación de ingreso</div>
                            </div>
                        </div>
                    </div>

                    {/* Pie institucional */}
                    <div className="relative z-20 flex items-center justify-between text-xs text-slate-500">
                        <span>© {new Date().getFullYear()} FitLife Technologies Inc.</span>
                        <div className="flex items-center gap-4">
                            <span className="hover:text-slate-400 transition cursor-pointer">Seguridad SSL 256-Bit</span>
                            <span>•</span>
                            <span className="hover:text-slate-400 transition cursor-pointer">Soporte Continuo</span>
                        </div>
                    </div>
                </div>

                {/* =========================================================================
                    COLUMNA DERECHA: PANEL DE FORMULARIO CON TABS CONMUTABLES Y MOBILE-FIRST
                    ========================================================================= */}
                <div className="lg:col-span-6 xl:col-span-6 flex flex-col justify-center px-4 sm:px-10 xl:px-16 py-8 md:py-12 bg-slate-950 relative overflow-y-auto">
                    {/* Resplandor decorativo */}
                    <div className="absolute top-0 right-1/4 h-72 w-72 rounded-full bg-lime-500/5 blur-3xl pointer-events-none" />

                    <div className="mx-auto w-full max-w-lg">
                        {/* Enlace para volver al Home en Desktop */}
                        <div className="hidden lg:flex items-center justify-between mb-4">
                            <a
                                href="/"
                                className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-lime-400 transition group"
                            >
                                <span className="group-hover:-translate-x-1 transition-transform">←</span>
                                <span>Volver al Inicio (Sitio Web)</span>
                            </a>
                            <span className="text-[11px] font-medium text-slate-500">FitLife Gym • Portal de Acceso</span>
                        </div>

                        {/* Cabecera en Móvil */}
                        <div className="mb-6 lg:hidden flex items-center justify-between">
                            <a href="/" className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-lime-400 to-emerald-500 shadow-md">
                                    <Dumbbell className="h-5 w-5 text-slate-950" />
                                </div>
                                <div>
                                    <span className="text-xl font-black tracking-tight text-white uppercase italic">
                                        FITLIFE <span className="text-lime-400 not-italic">OS</span>
                                    </span>
                                </div>
                            </a>
                            <a href="/" className="text-xs font-semibold text-lime-400 bg-lime-500/10 border border-lime-500/20 px-2.5 py-1 rounded-full hover:bg-lime-500/20 transition">
                                Ir al Inicio →
                            </a>
                        </div>

                        {/* CONMUTADOR DE PESTAÑAS (TABS CONMUTABLES) */}
                        <div className="mb-6 rounded-2xl border border-slate-800/90 bg-slate-900/90 p-1.5 backdrop-blur-md shadow-lg">
                            <div className="grid grid-cols-2 gap-1">
                                <button
                                    type="button"
                                    onClick={() => setPestañaActiva('login')}
                                    className={`relative flex items-center justify-center gap-2 rounded-xl py-2.5 text-xs sm:text-sm font-black uppercase tracking-wider transition-all duration-200 cursor-pointer ${
                                        pestañaActiva === 'login'
                                            ? 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.3)]'
                                            : 'text-slate-400 hover:text-white hover:bg-slate-800/60'
                                    }`}
                                >
                                    <KeyRound className="h-4 w-4" />
                                    <span>Iniciar Sesión</span>
                                </button>

                                <button
                                    type="button"
                                    onClick={() => setPestañaActiva('register')}
                                    className={`relative flex items-center justify-center gap-2 rounded-xl py-2.5 text-xs sm:text-sm font-black uppercase tracking-wider transition-all duration-200 cursor-pointer ${
                                        pestañaActiva === 'register'
                                            ? 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.3)]'
                                            : 'text-slate-400 hover:text-white hover:bg-slate-800/60'
                                    }`}
                                >
                                    <UserPlus className="h-4 w-4" />
                                    <span>Crear Cuenta</span>
                                </button>
                            </div>
                        </div>

                        {/* Alerta de Estado Flash */}
                        {status && (
                            <div className="mb-6 flex items-center gap-2.5 rounded-xl border border-lime-500/30 bg-lime-500/10 p-3.5 text-xs font-semibold text-lime-400">
                                <Check className="h-4 w-4 shrink-0" />
                                <span>{status}</span>
                            </div>
                        )}

                        {/* =========================================================================
                            PESTAÑA 1: INICIAR SESIÓN
                            ========================================================================= */}
                        {pestañaActiva === 'login' && (
                            <div className="animate-in fade-in duration-200">
                                <div className="mb-6">
                                    <h1 className="text-2xl sm:text-3xl font-black tracking-tight text-white">
                                        Bienvenido a FitLife OS
                                    </h1>
                                    <p className="mt-1 text-sm text-slate-400">
                                        Ingresa tus credenciales para acceder a tu panel de control o credencial de socio.
                                    </p>
                                </div>

                                <form onSubmit={submitLogin} className="space-y-4">
                                    {/* Campo Correo */}
                                    <div>
                                        <label
                                            htmlFor="login_email"
                                            className="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5"
                                        >
                                            Correo Electrónico
                                        </label>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                                <Mail className="h-4 w-4" />
                                            </div>
                                            <input
                                                id="login_email"
                                                type="email"
                                                required
                                                autoFocus
                                                autoComplete="username"
                                                value={formLogin.data.email}
                                                onChange={(e) => formLogin.setData('email', e.target.value)}
                                                placeholder="socio@fitlife.test"
                                                className={`w-full min-h-[44px] rounded-xl border bg-slate-900/90 py-2.5 pl-10 pr-4 text-sm text-white placeholder-slate-500 transition focus:outline-hidden ${
                                                    formLogin.errors.email
                                                        ? 'border-rose-500/80 ring-1 ring-rose-500/60 bg-rose-950/20'
                                                        : 'border-slate-800 focus:border-lime-400 focus:ring-1 focus:ring-lime-400'
                                                }`}
                                            />
                                        </div>
                                        {formLogin.errors.email && (
                                            <div className="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-rose-400">
                                                <AlertCircle className="h-3.5 w-3.5 shrink-0" />
                                                <span>{formLogin.errors.email}</span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Campo Contraseña con Toggle */}
                                    <div>
                                        <div className="flex items-center justify-between mb-1.5">
                                            <label
                                                htmlFor="login_password"
                                                className="block text-xs font-bold uppercase tracking-wider text-slate-300"
                                            >
                                                Contraseña
                                            </label>
                                            {canResetPassword && (
                                                <Link
                                                    href={route('password.request')}
                                                    className="text-xs font-semibold text-lime-400 hover:text-lime-300 transition"
                                                >
                                                    ¿Olvidaste tu contraseña?
                                                </Link>
                                            )}
                                        </div>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                                <Lock className="h-4 w-4" />
                                            </div>
                                            <input
                                                id="login_password"
                                                type={mostrarContrasenaLogin ? 'text' : 'password'}
                                                required
                                                autoComplete="current-password"
                                                value={formLogin.data.password}
                                                onChange={(e) => formLogin.setData('password', e.target.value)}
                                                placeholder="••••••••••••"
                                                className={`w-full min-h-[44px] rounded-xl border bg-slate-900/90 py-2.5 pl-10 pr-11 text-sm text-white placeholder-slate-500 transition focus:outline-hidden ${
                                                    formLogin.errors.password
                                                        ? 'border-rose-500/80 ring-1 ring-rose-500/60 bg-rose-950/20'
                                                        : 'border-slate-800 focus:border-lime-400 focus:ring-1 focus:ring-lime-400'
                                                }`}
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setMostrarContrasenaLogin(!mostrarContrasenaLogin)}
                                                className="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-white transition cursor-pointer"
                                                tabIndex={-1}
                                                title={mostrarContrasenaLogin ? 'Ocultar contraseña' : 'Ver contraseña'}
                                            >
                                                {mostrarContrasenaLogin ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                            </button>
                                        </div>
                                        {formLogin.errors.password && (
                                            <div className="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-rose-400">
                                                <AlertCircle className="h-3.5 w-3.5 shrink-0" />
                                                <span>{formLogin.errors.password}</span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Recordar Sesión */}
                                    <div className="flex items-center">
                                        <label className="flex items-center gap-2.5 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                checked={formLogin.data.remember}
                                                onChange={(e) => formLogin.setData('remember', e.target.checked)}
                                                className="h-4 w-4 rounded-sm border-slate-700 bg-slate-900 text-lime-500 focus:ring-lime-400 focus:ring-offset-slate-950"
                                            />
                                            <span className="text-xs font-medium text-slate-400 hover:text-slate-300">
                                                Mantener sesión abierta en este dispositivo
                                            </span>
                                        </label>
                                    </div>

                                    {/* Botón Submit */}
                                    <button
                                        type="submit"
                                        disabled={formLogin.processing}
                                        className="w-full min-h-[48px] rounded-xl bg-gradient-to-r from-lime-400 to-emerald-500 px-4 py-3 text-sm font-black uppercase tracking-wider text-slate-950 shadow-[0_0_25px_rgba(132,204,22,0.35)] hover:shadow-[0_0_35px_rgba(132,204,22,0.55)] transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                    >
                                        {formLogin.processing ? (
                                            <>
                                                <LoaderCircle className="h-4 w-4 animate-spin text-slate-950" />
                                                <span>Autenticando...</span>
                                            </>
                                        ) : (
                                            <>
                                                <span>Ingresar al Sistema</span>
                                                <ArrowRight className="h-4 w-4 stroke-[3]" />
                                            </>
                                        )}
                                    </button>
                                </form>

                                {/* ACCESOS RÁPIDOS DE DEMOSTRACIÓN */}
                                <div className="mt-8 pt-6 border-t border-slate-800/80">
                                    <div className="flex items-center justify-between mb-3">
                                        <span className="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                            <Zap className="h-3.5 w-3.5 text-lime-400" />
                                            Acceso Rápido por Rol (Demostración 1-Click)
                                        </span>
                                        <span className="text-[9px] font-mono font-bold text-slate-500">
                                            Pass: password
                                        </span>
                                    </div>

                                    <div className="grid grid-cols-3 gap-2">
                                        {/* Botón Admin */}
                                        <button
                                            type="button"
                                            disabled={rolEnProceso !== null}
                                            onClick={() => loginRapidoPorRol('admin@fitlife.test', 'admin')}
                                            className="group flex flex-col items-center justify-center rounded-xl border border-slate-800 bg-slate-900/60 p-2.5 transition hover:border-lime-500/50 hover:bg-slate-900 hover:shadow-[0_0_15px_rgba(132,204,22,0.2)] cursor-pointer disabled:opacity-50"
                                        >
                                            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-lime-400/10 text-lime-400 group-hover:scale-110 transition">
                                                {rolEnProceso === 'admin' ? (
                                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                                ) : (
                                                    <Shield className="h-4 w-4" />
                                                )}
                                            </div>
                                            <span className="mt-1 text-[11px] font-bold text-white">Admin</span>
                                            <span className="text-[9px] text-slate-400">Total</span>
                                        </button>

                                        {/* Botón Coach */}
                                        <button
                                            type="button"
                                            disabled={rolEnProceso !== null}
                                            onClick={() => loginRapidoPorRol('coach@fitlife.test', 'coach')}
                                            className="group flex flex-col items-center justify-center rounded-xl border border-slate-800 bg-slate-900/60 p-2.5 transition hover:border-emerald-500/50 hover:bg-slate-900 hover:shadow-[0_0_15px_rgba(16,185,129,0.2)] cursor-pointer disabled:opacity-50"
                                        >
                                            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-400/10 text-emerald-400 group-hover:scale-110 transition">
                                                {rolEnProceso === 'coach' ? (
                                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                                ) : (
                                                    <Activity className="h-4 w-4" />
                                                )}
                                            </div>
                                            <span className="mt-1 text-[11px] font-bold text-white">Coach</span>
                                            <span className="text-[9px] text-slate-400">Rutinas</span>
                                        </button>

                                        {/* Botón Socio */}
                                        <button
                                            type="button"
                                            disabled={rolEnProceso !== null}
                                            onClick={() => loginRapidoPorRol('socio@fitlife.test', 'socio')}
                                            className="group flex flex-col items-center justify-center rounded-xl border border-slate-800 bg-slate-900/60 p-2.5 transition hover:border-cyan-500/50 hover:bg-slate-900 hover:shadow-[0_0_15px_rgba(6,182,212,0.2)] cursor-pointer disabled:opacity-50"
                                        >
                                            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-400/10 text-cyan-400 group-hover:scale-110 transition">
                                                {rolEnProceso === 'socio' ? (
                                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                                ) : (
                                                    <Users className="h-4 w-4" />
                                                )}
                                            </div>
                                            <span className="mt-1 text-[11px] font-bold text-white">Socio</span>
                                            <span className="text-[9px] text-slate-400">Mi Perfil</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* =========================================================================
                            PESTAÑA 2: CREAR CUENTA (SIGN UP) DE SOCIO
                            ========================================================================= */}
                        {pestañaActiva === 'register' && (
                            <div className="animate-in fade-in duration-200">
                                <div className="mb-6">
                                    <h1 className="text-2xl sm:text-3xl font-black tracking-tight text-white">
                                        Crear Cuenta de Socio
                                    </h1>
                                    <p className="mt-1 text-sm text-slate-400">
                                        Regístrate para obtener tu carnet digital FitLife y activar tu membresía deportiva.
                                    </p>
                                </div>

                                <form onSubmit={submitRegistro} className="space-y-4">
                                    {/* Nombre Completo y Cédula/DNI en grid de 2 columnas en desktop */}
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label
                                                htmlFor="reg_name"
                                                className="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5"
                                            >
                                                Nombre Completo *
                                            </label>
                                            <div className="relative">
                                                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                                    <User className="h-4 w-4" />
                                                </div>
                                                <input
                                                    id="reg_name"
                                                    type="text"
                                                    required
                                                    autoFocus
                                                    value={formRegistro.data.name}
                                                    onChange={(e) => formRegistro.setData('name', e.target.value)}
                                                    placeholder="Carlos Mendoza"
                                                    className={`w-full min-h-[44px] rounded-xl border bg-slate-900/90 py-2 pl-10 pr-3 text-sm text-white placeholder-slate-500 transition focus:outline-hidden ${
                                                        formRegistro.errors.name
                                                            ? 'border-rose-500/80 ring-1 ring-rose-500/60 bg-rose-950/20'
                                                            : 'border-slate-800 focus:border-lime-400 focus:ring-1 focus:ring-lime-400'
                                                    }`}
                                                />
                                            </div>
                                            {formRegistro.errors.name && (
                                                <span className="mt-1 block text-xs font-medium text-rose-400">
                                                    {formRegistro.errors.name}
                                                </span>
                                            )}
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="reg_dni"
                                                className="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5"
                                            >
                                                Cédula / DNI *
                                            </label>
                                            <div className="relative">
                                                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                                    <IdCard className="h-4 w-4" />
                                                </div>
                                                <input
                                                    id="reg_dni"
                                                    type="text"
                                                    required
                                                    value={formRegistro.data.dni}
                                                    onChange={(e) => formRegistro.setData('dni', e.target.value)}
                                                    placeholder="V-28491023"
                                                    className={`w-full min-h-[44px] rounded-xl border bg-slate-900/90 py-2 pl-10 pr-3 text-sm text-white placeholder-slate-500 transition focus:outline-hidden ${
                                                        formRegistro.errors.dni
                                                            ? 'border-rose-500/80 ring-1 ring-rose-500/60 bg-rose-950/20'
                                                            : 'border-slate-800 focus:border-lime-400 focus:ring-1 focus:ring-lime-400'
                                                    }`}
                                                />
                                            </div>
                                            {formRegistro.errors.dni && (
                                                <span className="mt-1 block text-xs font-medium text-rose-400">
                                                    {formRegistro.errors.dni}
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Correo y Teléfono en grid */}
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label
                                                htmlFor="reg_email"
                                                className="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5"
                                            >
                                                Correo Electrónico *
                                            </label>
                                            <div className="relative">
                                                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                                    <Mail className="h-4 w-4" />
                                                </div>
                                                <input
                                                    id="reg_email"
                                                    type="email"
                                                    required
                                                    autoComplete="username"
                                                    value={formRegistro.data.email}
                                                    onChange={(e) => formRegistro.setData('email', e.target.value)}
                                                    placeholder="carlos@ejemplo.com"
                                                    className={`w-full min-h-[44px] rounded-xl border bg-slate-900/90 py-2 pl-10 pr-3 text-sm text-white placeholder-slate-500 transition focus:outline-hidden ${
                                                        formRegistro.errors.email
                                                            ? 'border-rose-500/80 ring-1 ring-rose-500/60 bg-rose-950/20'
                                                            : 'border-slate-800 focus:border-lime-400 focus:ring-1 focus:ring-lime-400'
                                                    }`}
                                                />
                                            </div>
                                            {formRegistro.errors.email && (
                                                <span className="mt-1 block text-xs font-medium text-rose-400">
                                                    {formRegistro.errors.email}
                                                </span>
                                            )}
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="reg_telefono"
                                                className="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5"
                                            >
                                                Teléfono Móvil
                                            </label>
                                            <div className="relative">
                                                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                                    <Phone className="h-4 w-4" />
                                                </div>
                                                <input
                                                    id="reg_telefono"
                                                    type="tel"
                                                    value={formRegistro.data.telefono}
                                                    onChange={(e) => formRegistro.setData('telefono', e.target.value)}
                                                    placeholder="+58 412 1234567"
                                                    className={`w-full min-h-[44px] rounded-xl border bg-slate-900/90 py-2 pl-10 pr-3 text-sm text-white placeholder-slate-500 transition focus:outline-hidden ${
                                                        formRegistro.errors.telefono
                                                            ? 'border-rose-500/80 ring-1 ring-rose-500/60 bg-rose-950/20'
                                                            : 'border-slate-800 focus:border-lime-400 focus:ring-1 focus:ring-lime-400'
                                                    }`}
                                                />
                                            </div>
                                            {formRegistro.errors.telefono && (
                                                <span className="mt-1 block text-xs font-medium text-rose-400">
                                                    {formRegistro.errors.telefono}
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Selector Opcional de Plan de Interés */}
                                    {planes.length > 0 && (
                                        <div>
                                            <label
                                                htmlFor="reg_plan_id"
                                                className="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5"
                                            >
                                                Plan de Membresía de Preferencia (Opcional)
                                            </label>
                                            <select
                                                id="reg_plan_id"
                                                value={formRegistro.data.plan_id}
                                                onChange={(e) => formRegistro.setData('plan_id', e.target.value)}
                                                className="w-full min-h-[44px] rounded-xl border border-slate-800 bg-slate-900/90 py-2 px-3 text-sm text-white focus:border-lime-400 focus:outline-hidden"
                                            >
                                                <option value="">Selecciona tu plan preferido...</option>
                                                {planes.map((plan) => (
                                                    <option key={plan.id} value={plan.id}>
                                                        {plan.nombre} — ${Number(plan.precio).toFixed(0)} USD ({plan.duracion_dias} días)
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    )}

                                    {/* Contraseña con Medidor de Fortaleza */}
                                    <div>
                                        <label
                                            htmlFor="reg_password"
                                            className="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5"
                                        >
                                            Contraseña de Acceso *
                                        </label>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                                <Lock className="h-4 w-4" />
                                            </div>
                                            <input
                                                id="reg_password"
                                                type={mostrarContrasenaReg ? 'text' : 'password'}
                                                required
                                                autoComplete="new-password"
                                                value={formRegistro.data.password}
                                                onChange={(e) => formRegistro.setData('password', e.target.value)}
                                                placeholder="Mínimo 8 caracteres"
                                                className={`w-full min-h-[44px] rounded-xl border bg-slate-900/90 py-2 pl-10 pr-11 text-sm text-white placeholder-slate-500 transition focus:outline-hidden ${
                                                    formRegistro.errors.password
                                                        ? 'border-rose-500/80 ring-1 ring-rose-500/60 bg-rose-950/20'
                                                        : 'border-slate-800 focus:border-lime-400 focus:ring-1 focus:ring-lime-400'
                                                }`}
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setMostrarContrasenaReg(!mostrarContrasenaReg)}
                                                className="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-white transition cursor-pointer"
                                                tabIndex={-1}
                                            >
                                                {mostrarContrasenaReg ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                            </button>
                                        </div>

                                        {/* INDICADOR VISUAL DE FORTALEZA */}
                                        {formRegistro.data.password && (
                                            <div className="mt-2 space-y-1">
                                                <div className="flex items-center justify-between text-[11px]">
                                                    <span className="text-slate-400">Seguridad de la clave:</span>
                                                    <span
                                                        className={`font-bold ${
                                                            fortaleza.score === 1
                                                                ? 'text-rose-400'
                                                                : fortaleza.score === 2
                                                                  ? 'text-amber-400'
                                                                  : 'text-lime-400'
                                                        }`}
                                                    >
                                                        {fortaleza.label}
                                                    </span>
                                                </div>
                                                <div className="grid grid-cols-3 gap-1.5 h-1.5">
                                                    <div
                                                        className={`rounded-full transition-colors duration-300 ${
                                                            fortaleza.score >= 1 ? fortaleza.color : 'bg-slate-800'
                                                        }`}
                                                    />
                                                    <div
                                                        className={`rounded-full transition-colors duration-300 ${
                                                            fortaleza.score >= 2 ? fortaleza.color : 'bg-slate-800'
                                                        }`}
                                                    />
                                                    <div
                                                        className={`rounded-full transition-colors duration-300 ${
                                                            fortaleza.score >= 3 ? fortaleza.color : 'bg-slate-800'
                                                        }`}
                                                    />
                                                </div>
                                            </div>
                                        )}

                                        {formRegistro.errors.password && (
                                            <span className="mt-1 block text-xs font-medium text-rose-400">
                                                {formRegistro.errors.password}
                                            </span>
                                        )}
                                    </div>

                                    {/* Confirmar Contraseña */}
                                    <div>
                                        <label
                                            htmlFor="reg_password_confirmation"
                                            className="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5"
                                        >
                                            Confirmar Contraseña *
                                        </label>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                                <Lock className="h-4 w-4" />
                                            </div>
                                            <input
                                                id="reg_password_confirmation"
                                                type={mostrarContrasenaConf ? 'text' : 'password'}
                                                required
                                                autoComplete="new-password"
                                                value={formRegistro.data.password_confirmation}
                                                onChange={(e) => formRegistro.setData('password_confirmation', e.target.value)}
                                                placeholder="Repite la contraseña"
                                                className="w-full min-h-[44px] rounded-xl border border-slate-800 bg-slate-900/90 py-2 pl-10 pr-11 text-sm text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setMostrarContrasenaConf(!mostrarContrasenaConf)}
                                                className="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-white transition cursor-pointer"
                                                tabIndex={-1}
                                            >
                                                {mostrarContrasenaConf ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                            </button>
                                        </div>
                                    </div>

                                    {/* Checkbox de Términos y Condiciones */}
                                    <div>
                                        <label className="flex items-start gap-2.5 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                required
                                                checked={formRegistro.data.terminos}
                                                onChange={(e) => formRegistro.setData('terminos', e.target.checked)}
                                                className="mt-0.5 h-4 w-4 rounded-sm border-slate-700 bg-slate-900 text-lime-500 focus:ring-lime-400 focus:ring-offset-slate-950"
                                            />
                                            <span className="text-xs text-slate-400 leading-snug">
                                                Acepto los{' '}
                                                <span className="text-lime-400 underline font-medium hover:text-lime-300">
                                                    Términos de Servicio y Reglamento
                                                </span>{' '}
                                                de FitLife Gym, así como la normativa de uso de instalaciones y acceso biométrico.
                                            </span>
                                        </label>
                                        {formRegistro.errors.terminos && (
                                            <span className="mt-1 block text-xs font-medium text-rose-400">
                                                {formRegistro.errors.terminos}
                                            </span>
                                        )}
                                    </div>

                                    {/* Botón Submit Registro */}
                                    <button
                                        type="submit"
                                        disabled={formRegistro.processing}
                                        className="w-full min-h-[48px] rounded-xl bg-gradient-to-r from-lime-400 to-emerald-500 px-4 py-3 text-sm font-black uppercase tracking-wider text-slate-950 shadow-[0_0_25px_rgba(132,204,22,0.35)] hover:shadow-[0_0_35px_rgba(132,204,22,0.55)] transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                    >
                                        {formRegistro.processing ? (
                                            <>
                                                <LoaderCircle className="h-4 w-4 animate-spin text-slate-950" />
                                                <span>Registrando Socio...</span>
                                            </>
                                        ) : (
                                            <>
                                                <UserCheck className="h-4 w-4 stroke-[2.5]" />
                                                <span>Completar Registro de Socio</span>
                                            </>
                                        )}
                                    </button>
                                </form>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

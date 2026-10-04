import BadgeVencimiento from '@/components/badge-vencimiento';
import CredencialDigitalSocio from '@/components/credencial-digital-socio';
import GraficoConcurrencia from '@/components/graficos/grafico-concurrencia';
import GraficoIngresosMembresias, { type IngresoPlanItem } from '@/components/graficos/grafico-ingresos-membresias';
import AppLayout from '@/layouts/app-layout';
import { useAppearance } from '@/hooks/use-appearance';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    AlertTriangle,
    ArrowUpRight,
    Bell,
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    DoorOpen,
    Dumbbell,
    Fingerprint,
    Flame,
    Gauge,
    Home,
    IdCard,
    Layers,
    LoaderCircle,
    LogOut,
    Maximize2,
    Moon,
    QrCode,
    RefreshCw,
    Scan,
    Search,
    ShieldAlert,
    ShieldCheck,
    ShieldX,
    SlidersHorizontal,
    Sparkles,
    Sun,
    Timer,
    TrendingUp,
    UserCheck,
    Users,
    X,
    XCircle,
    Zap,
} from 'lucide-react';
import React, { useState } from 'react';

interface Metricas {
    total_socios_activos: number;
    ingresos_mes: number;
    asistencias_hoy: number;
    asistencias_denegadas_hoy?: number;
    total_en_sala?: number;
    cuotas_vencidas: number;
    cuotas_por_vencer: number;
    altas_mes?: number;
    bajas_mes?: number;
    morosos_bloqueados_count?: number;
    recargos_clases_total?: number;
}

interface AsistenciaItem {
    id: number;
    fecha_hora: string;
    metodo: string;
    estado_acceso: string;
    mensaje_acceso?: string;
    sucursal?: {
        id: number;
        nombre: string;
    };
    socio?: {
        id: number;
        nombre: string;
        apellido: string;
        dni: string;
        codigo: string;
        foto?: string;
    };
}

interface SocioEnSala {
    id: number;
    socio: {
        id: number;
        nombre: string;
        apellido: string;
        dni: string;
        codigo: string;
        foto?: string;
    };
    fecha_hora_entrada: string;
    minutos_en_sala: number;
    metodo: string;
    sucursal: string;
}

interface ClaseHoyItem {
    id: number;
    nombre: string;
    horario: string;
    profesor: string;
    cupo_maximo: number;
    cupos_disponibles: number;
}

interface SucursalItem {
    id: number;
    nombre: string;
}

interface AlertaMembresia {
    id: number;
    socio: {
        id: number;
        nombre: string;
        apellido: string;
        codigo: string;
        dni: string;
        telefono?: string;
    };
    plan: string;
    fecha_fin: string;
    dias_restantes: number;
    esta_vencida: boolean;
}

interface PlanItem {
    id: number;
    nombre: string;
    precio: number | string;
    duracion_dias: number;
}

interface DashboardProps {
    metricas: Metricas;
    asistencias_recientes: AsistenciaItem[];
    alertas_membresias: AlertaMembresia[];
    planes: PlanItem[];
    concurrencia_hoy?: {
        etiquetas: string[];
        valores: number[];
    };
    ingresos_por_plan?: IngresoPlanItem[];
    socios_en_sala?: SocioEnSala[];
    clases_hoy?: ClaseHoyItem[];
    sucursales?: SucursalItem[];
    reportes?: any;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/',
    },
    {
        title: 'FitLife OS',
        href: '/dashboard',
    },
];

export default function Dashboard({
    metricas,
    asistencias_recientes = [],
    alertas_membresias = [],
    planes = [],
    concurrencia_hoy,
    ingresos_por_plan,
    socios_en_sala = [],
    clases_hoy = [],
    sucursales = [],
}: DashboardProps) {
    const page = usePage<any>();
    const usuarioAuth = page.props.auth?.user;
    const flash = page.props.flash;
    const { appearance, updateAppearance } = useAppearance();
    const esModoOscuro =
        appearance === 'dark' ||
        (appearance === 'system' && typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    // Estado del terminal de escaneo interactivo y control de acceso
    const [codigoAcceso, setCodigoAcceso] = useState('');
    const [procesandoAcceso, setProcesandoAcceso] = useState(false);
    const [resultadoAcceso, setResultadoAcceso] = useState<any>(flash?.resultado_acceso || null);

    // Opciones avanzadas de Control de Acceso
    const [tabAccesoActiva, setTabAccesoActiva] = useState<'escaner' | 'en_sala' | 'recientes'>('escaner');
    const [modoPaso, setModoPaso] = useState<'auto' | 'salida'>('auto');
    const [tipoAcceso, setTipoAcceso] = useState<'general' | 'clase_grupal'>('general');
    const [metodoAcceso, setMetodoAcceso] = useState<'qr' | 'codigo' | 'biometrico' | 'manual'>('qr');
    const [claseSeleccionadaId, setClaseSeleccionadaId] = useState<number | string>(clases_hoy[0]?.id || '');
    const [torniqueteSeleccionado, setTorniqueteSeleccionado] = useState<string>('Torniquete A1 (Entrada)');
    const [torniqueteAbierto, setTorniqueteAbierto] = useState<boolean>(false);
    const [temporizadorTorniquete, setTemporizadorTorniquete] = useState<number>(0);
    const [modoEmergencia, setModoEmergencia] = useState<boolean>(false);
    const [procesandoSalidaId, setProcesandoSalidaId] = useState<number | null>(null);

    // Modal de renovación rápida
    const [socioSeleccionado, setSocioSeleccionado] = useState<any>(null);
    const [planSeleccionado, setPlanSeleccionado] = useState<number>(planes[0]?.id || 1);
    const [metodoPago, setMetodoPago] = useState<string>('efectivo');
    const [procesandoRenovacion, setProcesandoRenovacion] = useState(false);

    // Estados de generadores automáticos 1-click
    const [generandoSocio, setGenerandoSocio] = useState(false);
    const [generandoAsistencia, setGenerandoAsistencia] = useState(false);
    const [generandoRutina, setGenerandoRutina] = useState(false);

    // Formateadores numéricos
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(amount);
    };

    const activarAperturaTorniquete = () => {
        setTorniqueteAbierto(true);
        setTemporizadorTorniquete(5);
        const intervalo = setInterval(() => {
            setTemporizadorTorniquete((prev) => {
                if (prev <= 1) {
                    clearInterval(intervalo);
                    setTorniqueteAbierto(false);
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
    };

    const alternarModoEmergencia = () => {
        if (!modoEmergencia) {
            setModoEmergencia(true);
            setTorniqueteAbierto(true);
            setResultadoAcceso({
                exito: true,
                tipo_evento: 'emergencia',
                estado_acceso: 'desbloqueo_emergencia',
                mensaje: 'MODO EVACUACIÓN / LIBRE: Torniquetes abiertos sin restricción.',
            });
        } else {
            setModoEmergencia(false);
            setTorniqueteAbierto(false);
            setTemporizadorTorniquete(0);
            setResultadoAcceso(null);
        }
    };

    const ejecutarCheckOutSocio = (asistenciaId: number, codigoSocio?: string) => {
        setProcesandoSalidaId(asistenciaId);
        router.post(
            route('asistencias.checkout'),
            {
                asistencia_id: asistenciaId,
                codigo: codigoSocio,
            },
            {
                preserveScroll: true,
                onSuccess: (pageProps: any) => {
                    const res = pageProps.props.flash?.resultado_acceso;
                    if (res) {
                        setResultadoAcceso(res);
                        activarAperturaTorniquete();
                    }
                    setProcesandoSalidaId(null);
                },
                onError: () => {
                    setProcesandoSalidaId(null);
                },
            }
        );
    };

    const ejecutarEscaneo = (codigoAProcesar?: string, forzarSalida = false) => {
        const codigoFinal = codigoAProcesar || codigoAcceso.trim();
        if (!codigoFinal) return;

        setProcesandoAcceso(true);

        if (forzarSalida || modoPaso === 'salida') {
            router.post(
                route('asistencias.checkout'),
                {
                    codigo: codigoFinal,
                },
                {
                    preserveScroll: true,
                    onSuccess: (pageProps: any) => {
                        const res = pageProps.props.flash?.resultado_acceso;
                        if (res) {
                            setResultadoAcceso(res);
                            activarAperturaTorniquete();
                        }
                        setCodigoAcceso('');
                        setProcesandoAcceso(false);
                    },
                    onError: (errors: any) => {
                        setResultadoAcceso({
                            exito: false,
                            estado_acceso: 'denegado_error',
                            mensaje: errors.codigo || 'No se pudo registrar la salida del socio o no presenta entrada abierta hoy.',
                        });
                        setProcesandoAcceso(false);
                    },
                }
            );
            return;
        }

        router.post(
            route('asistencias.acceso'),
            {
                codigo: codigoFinal,
                metodo: metodoAcceso,
                tipo_acceso: tipoAcceso,
                clase_id: tipoAcceso === 'clase_grupal' && claseSeleccionadaId ? claseSeleccionadaId : undefined,
            },
            {
                preserveScroll: true,
                onSuccess: (pageProps: any) => {
                    const res = pageProps.props.flash?.resultado_acceso;
                    if (res) {
                        setResultadoAcceso(res);
                        if (res.exito) {
                            activarAperturaTorniquete();
                        }
                    }
                    setCodigoAcceso('');
                    setProcesandoAcceso(false);
                },
                onError: (errors: any) => {
                    setResultadoAcceso({
                        exito: false,
                        estado_acceso: 'denegado_error',
                        mensaje: errors.codigo || 'Código de socio no válido o no registrado.',
                    });
                    setProcesandoAcceso(false);
                },
            }
        );
    };

    const handleFormSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        ejecutarEscaneo();
    };

    const handleRenovacion = (e: React.FormEvent) => {
        e.preventDefault();
        if (!socioSeleccionado) return;

        setProcesandoRenovacion(true);
        router.post(
            route('membresias.store'),
            {
                socio_id: socioSeleccionado.id,
                plan_id: planSeleccionado,
                metodo_pago: metodoPago,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setProcesandoRenovacion(false);
                    setSocioSeleccionado(null);
                },
                onError: () => {
                    setProcesandoRenovacion(false);
                },
            }
        );
    };

    const nombreRol =
        usuarioAuth?.rol?.nombre_mostrar || (usuarioAuth?.rol_id === 1 ? 'Administrador' : 'Coach');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="FitLife Pro • Dashboard Deportivo de Alta Gama" />

            <div className="min-h-full bg-slate-100/80 dark:bg-slate-950 text-slate-800 dark:text-slate-100 p-4 md:p-8 font-sans selection:bg-lime-500 selection:text-black transition-colors duration-200">
                {/* 1. HEADER SUPERIOR DE ALTA GAMA */}
                <header className="relative mb-8 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white/95 dark:bg-gradient-to-r dark:from-slate-900/90 dark:via-slate-900/60 dark:to-slate-950 p-5 shadow-sm dark:shadow-2xl backdrop-blur-xl transition-colors duration-200">
                    <div className="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-lime-500/40 to-transparent"></div>

                    <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-400 to-emerald-600 shadow-[0_0_25px_rgba(132,204,22,0.35)]">
                                <Dumbbell className="h-7 w-7 text-slate-950" />
                                <span className="absolute -bottom-1 -right-1 flex h-4 w-4">
                                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-lime-400 opacity-75"></span>
                                    <span className="relative inline-flex h-4 w-4 rounded-full border-2 border-white dark:border-slate-950 bg-lime-400"></span>
                                </span>
                            </div>

                            <div>
                                <div className="flex items-center gap-2.5">
                                    <h1 className="text-xl md:text-2xl font-black tracking-tight text-slate-950 dark:text-white uppercase italic">
                                        FITLIFE <span className="text-transparent bg-clip-text bg-gradient-to-r from-lime-500 to-emerald-500 dark:from-lime-400 dark:to-emerald-400 font-extrabold not-italic">OS PRO</span>
                                    </h1>
                                    <span className="rounded-full border border-lime-500/30 bg-lime-500/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest text-lime-600 dark:text-lime-400">
                                        v2.4 Sports
                                    </span>
                                </div>
                                <p className="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span className="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Servidor Central Online • <span className="text-slate-700 dark:text-slate-300 font-medium">Sede Principal</span> •
                                    <span className="hidden sm:inline text-slate-400 dark:text-slate-500">
                                        {new Date().toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-2.5 sm:gap-3">
                            {/* Botón Ir al Home Page */}
                            <Link
                                href="/"
                                className="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900/90 px-3 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 transition hover:border-lime-500/50 hover:bg-slate-200 dark:hover:bg-slate-800 hover:text-slate-950 dark:hover:text-white shadow-xs group"
                                title="Volver a la Página de Inicio (Home)"
                            >
                                <Home className="h-4 w-4 text-lime-600 dark:text-lime-400 transition-transform duration-200 group-hover:scale-110" />
                                <span>Home Page</span>
                            </Link>

                            {/* Aforo en Tiempo Real */}
                            <div className="hidden sm:flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 px-3.5 py-2 shadow-xs">
                                <div className="p-1.5 rounded-lg bg-lime-500/10 text-lime-600 dark:text-lime-400">
                                    <Gauge className="h-4 w-4" />
                                </div>
                                <div>
                                    <div className="flex items-center justify-between text-[11px] font-semibold gap-3">
                                        <span className="text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[9px]">Aforo en Sala</span>
                                        <span className="text-lime-600 dark:text-lime-400 font-mono font-bold">
                                            {metricas.total_en_sala ?? 0} atletas ({Math.min(100, Math.round(((metricas.total_en_sala ?? 0) / 120) * 100))}%)
                                        </span>
                                    </div>
                                    <div className="mt-1 h-1.5 w-28 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                        <div
                                            className="h-full rounded-full bg-gradient-to-r from-lime-400 to-emerald-500 shadow-[0_0_8px_rgba(132,204,22,0.6)]"
                                            style={{ width: `${Math.max(6, Math.min(100, Math.round(((metricas.total_en_sala ?? 0) / 120) * 100)))}%` }}
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            {/* Botón de Notificaciones */}
                            <div className="relative">
                                <button
                                    type="button"
                                    className="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900/90 text-slate-600 dark:text-slate-300 transition hover:border-slate-300 dark:hover:border-slate-700 hover:text-slate-950 dark:hover:text-white shadow-xs"
                                    title="Notificaciones y Alertas"
                                >
                                    <Bell className="h-4 w-4" />
                                    {alertas_membresias.length > 0 && (
                                        <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-lg shadow-rose-500/50">
                                            {alertas_membresias.length}
                                        </span>
                                    )}
                                </button>
                            </div>

                            {/* Perfil del Usuario Autenticado */}
                            <div className="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 p-1.5 pr-4 shadow-xs">
                                <div className="relative flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-800 dark:to-slate-700 text-xs font-bold text-slate-800 dark:text-white ring-1 ring-lime-500/40">
                                    {usuarioAuth?.name ? usuarioAuth.name.charAt(0) : 'A'}
                                </div>
                                <div className="text-left">
                                    <div className="text-xs font-bold text-slate-900 dark:text-white leading-tight">
                                        {usuarioAuth?.name || 'Administrador'}
                                    </div>
                                    <div className="flex items-center gap-1.5 mt-0.5">
                                        <span className="inline-flex items-center rounded-md bg-lime-500/10 px-1.5 py-0.2 text-[9px] font-extrabold uppercase tracking-wide text-lime-700 dark:text-lime-400 border border-lime-500/20">
                                            {nombreRol}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Separador vertical */}
                            <div className="hidden sm:block h-6 w-px bg-slate-200 dark:bg-slate-800"></div>

                            {/* Botón Alternar Modo Claro / Oscuro en la Esquina Derecha */}
                            <button
                                type="button"
                                onClick={() => updateAppearance(esModoOscuro ? 'light' : 'dark')}
                                className="inline-flex items-center justify-center h-10 w-10 sm:w-auto sm:px-3 sm:py-2 gap-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900/90 text-xs font-bold text-slate-700 dark:text-slate-200 transition hover:border-lime-500/50 hover:bg-slate-200 dark:hover:bg-slate-800 hover:text-slate-950 dark:hover:text-white shadow-xs cursor-pointer shrink-0"
                                title={esModoOscuro ? 'Cambiar a Tema Blanco / Claro' : 'Cambiar a Tema Oscuro'}
                            >
                                {esModoOscuro ? (
                                    <Sun className="h-4 w-4 text-amber-400" />
                                ) : (
                                    <Moon className="h-4 w-4 text-slate-700" />
                                )}
                                <span className="hidden sm:inline">{esModoOscuro ? 'Claro' : 'Oscuro'}</span>
                            </button>
                        </div>
                    </div>
                </header>

                {/* BARRA DE ACCIÓN RÁPIDA: GENERADORES AUTOMÁTICOS 1-CLICK */}
                <div className="mb-8 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white/95 dark:bg-gradient-to-r dark:from-slate-900/95 dark:via-slate-900/80 dark:to-slate-950 p-4 shadow-xs dark:shadow-xl flex flex-col md:flex-row items-center justify-between gap-4 transition-colors duration-200">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-500/10 border border-lime-500/30 text-lime-600 dark:text-lime-400 shadow-xs">
                            <Zap className="h-5 w-5" />
                        </div>
                        <div>
                            <h3 className="text-sm font-black uppercase tracking-wider text-slate-950 dark:text-white flex items-center gap-2">
                                Automatización Rápida
                                <span className="rounded-full bg-lime-500/10 border border-lime-500/20 px-2 py-0.5 text-[9px] font-mono font-bold text-lime-700 dark:text-lime-400">1-CLICK</span>
                            </h3>
                            <p className="text-xs text-slate-500 dark:text-slate-400">Genera registros realistas instantáneamente en los 3 módulos del sistema.</p>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                        <button
                            type="button"
                            disabled={generandoSocio}
                            onClick={() => {
                                setGenerandoSocio(true);
                                router.post(route('socios.generar-automatico'), {}, {
                                    preserveScroll: true,
                                    onFinish: () => setGenerandoSocio(false),
                                });
                            }}
                            className="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-3.5 py-2 text-xs font-bold text-emerald-400 shadow-sm hover:bg-emerald-500/20 hover:border-emerald-400 transition cursor-pointer disabled:opacity-50"
                            title="Genera un nuevo socio con Faker en español, plan activo y pago"
                        >
                            {generandoSocio ? <LoaderCircle className="h-3.5 w-3.5 animate-spin text-emerald-400" /> : <Users className="h-3.5 w-3.5" />}
                            <span>{generandoSocio ? 'Creando...' : '⚡ Socio Auto'}</span>
                        </button>

                        <button
                            type="button"
                            disabled={generandoAsistencia}
                            onClick={() => {
                                setGenerandoAsistencia(true);
                                router.post(route('asistencias.generar-automatica'), {}, {
                                    preserveScroll: true,
                                    onFinish: () => setGenerandoAsistencia(false),
                                });
                            }}
                            className="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 rounded-xl border border-lime-500/40 bg-lime-500/10 px-3.5 py-2 text-xs font-bold text-lime-400 shadow-sm hover:bg-lime-500/20 hover:border-lime-400 transition cursor-pointer disabled:opacity-50"
                            title="Simula una asistencia en tiempo real de un socio activo"
                        >
                            {generandoAsistencia ? <LoaderCircle className="h-3.5 w-3.5 animate-spin text-lime-400" /> : <QrCode className="h-3.5 w-3.5" />}
                            <span>{generandoAsistencia ? 'Marcando...' : '⚡ Asistencia Auto'}</span>
                        </button>

                        <button
                            type="button"
                            disabled={generandoRutina}
                            onClick={() => {
                                setGenerandoRutina(true);
                                router.post(route('rutinas.generar-automatica'), {}, {
                                    preserveScroll: true,
                                    onFinish: () => setGenerandoRutina(false),
                                });
                            }}
                            className="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-500/10 px-3.5 py-2 text-xs font-bold text-cyan-400 shadow-sm hover:bg-cyan-500/20 hover:border-cyan-400 transition cursor-pointer disabled:opacity-50"
                            title="Genera una rutina técnica asignada a un socio basada en plantillas"
                        >
                            {generandoRutina ? <LoaderCircle className="h-3.5 w-3.5 animate-spin text-cyan-400" /> : <Dumbbell className="h-3.5 w-3.5" />}
                            <span>{generandoRutina ? 'Diseñando...' : '⚡ Rutina Auto'}</span>
                        </button>
                    </div>
                </div>

                {/* 2. TARJETAS DE KPIS */}
                <section className="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {/* KPI 1 */}
                    <div className="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/95 dark:to-slate-950 p-5 shadow-xs dark:shadow-xl transition-all duration-300 hover:border-lime-500/40 hover:shadow-md dark:hover:shadow-[0_0_30px_rgba(132,204,22,0.12)]">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Socios Activos
                            </span>
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-lime-500/30 bg-lime-500/10 text-lime-600 dark:text-lime-400 shadow-xs transition group-hover:scale-110">
                                <Users className="h-5 w-5" />
                            </div>
                        </div>

                        <div className="mt-4 flex items-baseline justify-between">
                            <div className="text-3xl md:text-4xl font-black tracking-tight text-slate-950 dark:text-white font-mono">
                                {metricas.total_socios_activos}
                            </div>
                            <span className="flex items-center text-xs font-bold text-lime-700 dark:text-lime-400 bg-lime-500/10 border border-lime-500/20 px-2 py-0.5 rounded-md">
                                <ArrowUpRight className="mr-0.5 h-3.5 w-3.5" />
                                +14.2%
                            </span>
                        </div>

                        <div className="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-800/60">
                            <span className="flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                <Sparkles className="h-3 w-3" /> Membresías vigentes
                            </span>
                            <svg className="h-5 w-16 text-lime-600 dark:text-lime-400 opacity-80" viewBox="0 0 64 20" fill="none">
                                <path d="M0 16 L12 14 L24 10 L36 12 L48 6 L64 2" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" />
                            </svg>
                        </div>
                    </div>

                    {/* KPI 2 */}
                    <div className="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/95 dark:to-slate-950 p-5 shadow-xs dark:shadow-xl transition-all duration-300 hover:border-emerald-500/40 hover:shadow-md dark:hover:shadow-[0_0_30px_rgba(16,185,129,0.12)]">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Recaudación Mensual
                            </span>
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shadow-xs transition group-hover:scale-110">
                                <DollarSign className="h-5 w-5" />
                            </div>
                        </div>

                        <div className="mt-4 flex items-baseline justify-between">
                            <div className="text-3xl md:text-4xl font-black tracking-tight text-slate-950 dark:text-white font-mono">
                                {formatCurrency(Number(metricas.ingresos_mes))}
                            </div>
                            <span className="flex items-center text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md">
                                <TrendingUp className="mr-0.5 h-3.5 w-3.5" />
                                +8.9%
                            </span>
                        </div>

                        <div className="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-800/60">
                            <span className="text-[11px] text-slate-500 dark:text-slate-400">Meta: 92% cumplida</span>
                            <div className="h-1.5 w-16 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                <div className="h-full bg-emerald-500 dark:bg-emerald-400 rounded-full w-[92%]"></div>
                            </div>
                        </div>
                    </div>

                    {/* KPI 3 */}
                    <div className="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/95 dark:to-slate-950 p-5 shadow-xs dark:shadow-xl transition-all duration-300 hover:border-cyan-500/40 hover:shadow-md dark:hover:shadow-[0_0_30px_rgba(6,182,212,0.12)]">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Asistencias del Día
                            </span>
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-cyan-500/30 bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 shadow-xs transition group-hover:scale-110">
                                <Zap className="h-5 w-5" />
                            </div>
                        </div>

                        <div className="mt-4 flex items-baseline justify-between">
                            <div className="text-3xl md:text-4xl font-black tracking-tight text-slate-950 dark:text-white font-mono">
                                {metricas.asistencias_hoy}
                            </div>
                            <span className="flex items-center text-xs font-bold text-cyan-700 dark:text-cyan-400 bg-cyan-500/10 border border-cyan-500/20 px-2 py-0.5 rounded-md">
                                <Flame className="mr-0.5 h-3.5 w-3.5" />
                                Turno Activo
                            </span>
                        </div>

                        <div className="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-800/60">
                            <span className="text-[11px] text-slate-500 dark:text-slate-400">Pico horario: 18:00 - 21:00</span>
                            <span className="text-[11px] font-mono font-bold text-cyan-600 dark:text-cyan-400">Torniquete #1</span>
                        </div>
                    </div>

                    {/* KPI 4 */}
                    <div className="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/95 dark:to-slate-950 p-5 shadow-xs dark:shadow-xl transition-all duration-300 hover:border-rose-500/40 hover:shadow-md dark:hover:shadow-[0_0_30px_rgba(244,63,94,0.12)]">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Cuotas en Vencimiento
                            </span>
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-400 shadow-xs transition group-hover:scale-110">
                                <ShieldAlert className="h-5 w-5" />
                            </div>
                        </div>

                        <div className="mt-4 flex items-baseline justify-between">
                            <div className="text-3xl md:text-4xl font-black tracking-tight text-rose-600 dark:text-rose-400 font-mono">
                                {metricas.cuotas_vencidas}
                            </div>
                            <span className="flex items-center text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md">
                                {metricas.cuotas_por_vencer} por vencer
                            </span>
                        </div>

                        <div className="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-800/60">
                            <span className="text-[11px] text-rose-600 dark:text-rose-400 flex items-center gap-1 font-semibold">
                                <AlertTriangle className="h-3 w-3" /> Requiere cobranza
                            </span>
                            <span className="text-[11px] font-medium text-slate-400 dark:text-slate-500">Gestión automática</span>
                        </div>
                    </div>
                </section>

                {/* 3. SECCIÓN DE ANÁLISIS ESTADÍSTICO FITLIFE ANALYTICS (CHART.JS) */}
                <section className="mb-8 grid gap-6 lg:grid-cols-12 items-stretch">
                    <div className="lg:col-span-7">
                        <GraficoConcurrencia datos={concurrencia_hoy} />
                    </div>
                    <div className="lg:col-span-5">
                        <GraficoIngresosMembresias datos={ingresos_por_plan} />
                    </div>
                </section>

                {/* 4. SECCIÓN CENTRAL: WIDGET DE ESCÁNER BIOMÉTRICO CON CREDENCIAL DIGITAL 3D TILT + AUDITORÍA */}
                <section className="mb-8 grid gap-6 lg:grid-cols-12">
                    {/* WIDGET INTEGRAL DE CONTROL DE ACCESO & TORNIQUETES */}
                    <div className="lg:col-span-7 rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/90 dark:to-slate-950 p-6 shadow-xs dark:shadow-2xl backdrop-blur-md flex flex-col justify-between relative overflow-hidden transition-colors duration-200">
                        <div className="absolute top-0 right-0 h-48 w-48 rounded-full bg-lime-500/5 blur-3xl pointer-events-none"></div>

                        <div>
                            {/* Cabecera del Widget con Estado de Hardware y Accesos Directos */}
                            <div className="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-4 gap-3">
                                <div className="flex items-center gap-3">
                                    <div className={`flex h-10 w-10 items-center justify-center rounded-xl border shadow-xs transition-colors ${
                                        modoEmergencia
                                            ? 'bg-amber-500/20 border-amber-500/40 text-amber-500 dark:text-amber-400 shadow-[0_0_20px_rgba(245,158,11,0.3)] animate-pulse'
                                            : torniqueteAbierto
                                                ? 'bg-lime-500/20 border-lime-500/50 text-lime-600 dark:text-lime-400 shadow-[0_0_20px_rgba(132,204,22,0.4)]'
                                                : 'bg-lime-500/10 border-lime-500/30 text-lime-600 dark:text-lime-400'
                                    }`}>
                                        <Scan className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <div className="flex items-center gap-2">
                                            <h2 className="text-base font-black text-slate-950 dark:text-white uppercase tracking-wide">
                                                Control de Acceso
                                            </h2>
                                            <span className="text-[10px] text-lime-700 dark:text-lime-400 font-mono font-bold bg-lime-500/10 border border-lime-500/20 px-2 py-0.5 rounded-md">
                                                {torniqueteSeleccionado.split(' ')[0]}
                                            </span>
                                        </div>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Torniquete biométrico, carnet digital QR y validación de aforo
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    {/* Indicador de Hardware */}
                                    {modoEmergencia ? (
                                        <span className="inline-flex items-center gap-1.5 rounded-full border border-amber-500/40 bg-amber-500/10 px-2.5 py-1 text-[10px] font-bold text-amber-500 dark:text-amber-400 animate-pulse">
                                            <AlertTriangle className="h-3 w-3" />
                                            EVACUACIÓN
                                        </span>
                                    ) : torniqueteAbierto ? (
                                        <span className="inline-flex items-center gap-1.5 rounded-full border border-lime-500/40 bg-lime-500/20 px-2.5 py-1 text-[10px] font-black text-lime-600 dark:text-lime-400 animate-bounce">
                                            <DoorOpen className="h-3 w-3" />
                                            ABIERTO ({temporizadorTorniquete}s)
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                            <span className="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                                            ONLINE
                                        </span>
                                    )}

                                    {/* Botón Lanzar Modo Kiosco */}
                                    <a
                                        href="/asistencias/kiosco"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1 text-[11px] font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600 transition shadow-xs"
                                        title="Abrir Modo Kiosco en pantalla completa (Recepción)"
                                    >
                                        <Maximize2 className="h-3.5 w-3.5 text-lime-600 dark:text-lime-400" />
                                        <span className="hidden sm:inline">Kiosco</span>
                                    </a>

                                    {/* Botón Emergencia / Desbloqueo libre */}
                                    <button
                                        type="button"
                                        onClick={alternarModoEmergencia}
                                        className={`inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-[11px] font-bold transition cursor-pointer shadow-xs ${
                                            modoEmergencia
                                                ? 'border-amber-500 bg-amber-500 text-slate-950 shadow-[0_0_15px_rgba(245,158,11,0.5)]'
                                                : 'border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-amber-500 hover:border-amber-500/30'
                                        }`}
                                        title={modoEmergencia ? 'Desactivar modo evacuación' : 'Abrir torniquete manualmente (Emergencia)'}
                                    >
                                        <AlertTriangle className="h-3.5 w-3.5" />
                                        <span className="hidden sm:inline">{modoEmergencia ? 'Liberado' : 'Emergencia'}</span>
                                    </button>
                                </div>
                            </div>

                            {/* PESTAÑAS DEL WIDGET */}
                            <div className="mt-4 flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 dark:border-slate-800/60 pb-3 gap-2">
                                <div className="flex items-center gap-1 bg-slate-100 dark:bg-slate-950/60 p-1 rounded-xl border border-slate-200 dark:border-slate-800/80">
                                    <button
                                        type="button"
                                        onClick={() => setTabAccesoActiva('escaner')}
                                        className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer ${
                                            tabAccesoActiva === 'escaner'
                                                ? 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 shadow-md'
                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-800/50'
                                        }`}
                                    >
                                        <Zap className="h-3.5 w-3.5" />
                                        <span>Torniquete & Escáner</span>
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => setTabAccesoActiva('en_sala')}
                                        className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer ${
                                            tabAccesoActiva === 'en_sala'
                                                ? 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 shadow-md'
                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-800/50'
                                        }`}
                                    >
                                        <Users className="h-3.5 w-3.5" />
                                        <span>Atletas en Sala</span>
                                        <span className={`ml-1 rounded-full px-1.5 py-0.2 text-[10px] font-mono ${
                                            tabAccesoActiva === 'en_sala'
                                                ? 'bg-slate-950 text-lime-400 font-bold'
                                                : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                        }`}>
                                            {socios_en_sala.length}
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => setTabAccesoActiva('recientes')}
                                        className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer ${
                                            tabAccesoActiva === 'recientes'
                                                ? 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 shadow-md'
                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-800/50'
                                        }`}
                                    >
                                        <Activity className="h-3.5 w-3.5" />
                                        <span>Historial</span>
                                    </button>
                                </div>

                                {/* Selección de Torniquete / Punto de Acceso */}
                                <div className="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                                    <SlidersHorizontal className="h-3.5 w-3.5 text-lime-600 dark:text-lime-400" />
                                    <select
                                        value={torniqueteSeleccionado}
                                        onChange={(e) => setTorniqueteSeleccionado(e.target.value)}
                                        className="rounded-lg border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 px-2 py-1 text-xs text-slate-800 dark:text-slate-300 focus:border-lime-500 dark:focus:border-lime-400 focus:outline-hidden"
                                    >
                                        <option value="Torniquete A1 (Entrada)">Torniquete A1 (Entrada)</option>
                                        <option value="Torniquete B2 (Salida Rápida)">Torniquete B2 (Salida)</option>
                                        <option value="Molinete Clases Grupales">Molinete Clases</option>
                                        <option value="Acceso Kiosco Recepción">Acceso Kiosco</option>
                                    </select>
                                </div>
                            </div>

                            {/* CONTENIDO DE PESTAÑA 1: TORNIQUETE Y ESCÁNER */}
                            {tabAccesoActiva === 'escaner' && (
                                <div className="mt-4 animate-in fade-in duration-200">
                                    {/* Barra de Opciones de Validación (Modo, Tipo y Método) */}
                                    <div className="mb-4 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50/80 dark:bg-slate-950/60 p-3 space-y-3">
                                        <div className="flex flex-wrap items-center justify-between gap-2.5">
                                            {/* Modo: Entrada vs Salida */}
                                            <div className="flex items-center gap-1 bg-slate-200/80 dark:bg-slate-900 p-1 rounded-lg border border-slate-300 dark:border-slate-800">
                                                <button
                                                    type="button"
                                                    onClick={() => setModoPaso('auto')}
                                                    className={`flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-bold transition cursor-pointer ${
                                                        modoPaso === 'auto'
                                                            ? 'bg-lime-500/20 text-lime-700 dark:text-lime-400 border border-lime-500/30'
                                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white'
                                                    }`}
                                                >
                                                    <Zap className="h-3 w-3" />
                                                    Entrada (Check-In)
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setModoPaso('salida')}
                                                    className={`flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-bold transition cursor-pointer ${
                                                        modoPaso === 'salida'
                                                            ? 'bg-cyan-500/20 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30'
                                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white'
                                                    }`}
                                                >
                                                    <LogOut className="h-3 w-3" />
                                                    Salida (Check-Out)
                                                </button>
                                            </div>

                                            {/* Tipo de Acceso: General vs Clase Grupal */}
                                            {modoPaso === 'auto' && (
                                                <div className="flex items-center gap-1 bg-slate-200/80 dark:bg-slate-900 p-1 rounded-lg border border-slate-300 dark:border-slate-800">
                                                    <button
                                                        type="button"
                                                        onClick={() => setTipoAcceso('general')}
                                                        className={`rounded-md px-2.5 py-1 text-xs font-bold transition cursor-pointer ${
                                                            tipoAcceso === 'general'
                                                                ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white border border-slate-300 dark:border-slate-700 shadow-xs'
                                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white'
                                                        }`}
                                                    >
                                                        Acceso General
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => setTipoAcceso('clase_grupal')}
                                                        className={`flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-bold transition cursor-pointer ${
                                                            tipoAcceso === 'clase_grupal'
                                                                ? 'bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/30'
                                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white'
                                                        }`}
                                                    >
                                                        <Flame className="h-3 w-3 text-purple-500 dark:text-purple-400" />
                                                        Clase Grupal
                                                    </button>
                                                </div>
                                            )}

                                            {/* Método de Lectura */}
                                            <div className="flex items-center gap-1 text-[11px]">
                                                <span className="text-slate-500 text-[10px] uppercase font-bold mr-1 hidden md:inline">Método:</span>
                                                {[
                                                    { id: 'qr', label: 'QR / Carnet', icon: QrCode },
                                                    { id: 'codigo', label: 'DNI / Código', icon: IdCard },
                                                    { id: 'biometrico', label: 'Biometría', icon: Fingerprint },
                                                    { id: 'manual', label: 'Manual', icon: UserCheck },
                                                ].map((m) => (
                                                    <button
                                                        key={m.id}
                                                        type="button"
                                                        onClick={() => setMetodoAcceso(m.id as any)}
                                                        className={`flex items-center gap-1 rounded-lg px-2 py-1 font-medium transition cursor-pointer ${
                                                            metodoAcceso === m.id
                                                                ? 'bg-white dark:bg-slate-800 text-lime-600 dark:text-lime-400 border border-slate-300 dark:border-slate-700 shadow-xs'
                                                                : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-300'
                                                        }`}
                                                    >
                                                        <m.icon className="h-3 w-3" />
                                                        <span className="hidden sm:inline">{m.label}</span>
                                                    </button>
                                                ))}
                                            </div>
                                        </div>

                                        {/* Selector de Clase Grupal si está seleccionado */}
                                        {tipoAcceso === 'clase_grupal' && modoPaso === 'auto' && (
                                            <div className="pt-2 border-t border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                                                <div className="flex items-center gap-2">
                                                    <span className="text-xs font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1">
                                                        <Flame className="h-3.5 w-3.5" />
                                                        Seleccionar Clase:
                                                    </span>
                                                    <select
                                                        value={claseSeleccionadaId}
                                                        onChange={(e) => setClaseSeleccionadaId(e.target.value)}
                                                        className="rounded-lg border border-purple-300 dark:border-purple-500/30 bg-purple-50 dark:bg-purple-950/30 px-2.5 py-1 text-xs text-purple-900 dark:text-purple-200 focus:border-purple-500 focus:outline-hidden"
                                                    >
                                                        {clases_hoy.length === 0 ? (
                                                            <option value="">No hay clases grupales programadas hoy</option>
                                                        ) : (
                                                            clases_hoy.map((c) => (
                                                                <option key={c.id} value={c.id}>
                                                                    {c.nombre} • {c.horario} (Prof. {c.profesor}) — {c.cupos_disponibles} cupos libres
                                                                </option>
                                                            ))
                                                        )}
                                                    </select>
                                                </div>
                                                <span className="text-[11px] text-slate-600 dark:text-slate-400">
                                                    * Plan Básico: No permitido | Plan Completo: Recargo $10 | Plan Premium: Incluido
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Visor con haz láser */}
                                    <div className="relative rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/90 p-5 overflow-hidden shadow-xs dark:shadow-inner">
                                        <div className="absolute top-2 left-2 h-4 w-4 border-t-2 border-l-2 border-lime-500 dark:border-lime-400"></div>
                                        <div className="absolute top-2 right-2 h-4 w-4 border-t-2 border-r-2 border-lime-500 dark:border-lime-400"></div>
                                        <div className="absolute bottom-2 left-2 h-4 w-4 border-b-2 border-l-2 border-lime-500 dark:border-lime-400"></div>
                                        <div className="absolute bottom-2 right-2 h-4 w-4 border-b-2 border-r-2 border-lime-500 dark:border-lime-400"></div>

                                        <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-lime-500 to-transparent shadow-[0_0_15px_rgba(132,204,22,0.8)] animate-[bounce_3s_infinite]"></div>

                                        <div className="flex flex-col items-center justify-center py-3 text-center">
                                            <div className="relative mb-2 flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 text-lime-600 dark:text-lime-400 shadow-inner">
                                                {metodoAcceso === 'biometrico' ? (
                                                    <Fingerprint className="h-7 w-7" />
                                                ) : metodoAcceso === 'codigo' ? (
                                                    <IdCard className="h-7 w-7" />
                                                ) : (
                                                    <QrCode className="h-7 w-7" />
                                                )}
                                                <div className="absolute inset-0 rounded-2xl bg-lime-400/5 animate-pulse"></div>
                                            </div>
                                            <p className="text-xs font-semibold text-slate-700 dark:text-slate-200">
                                                {modoPaso === 'salida'
                                                    ? 'Acerca carnet o ingresa código de socio para registrar egreso'
                                                    : metodoAcceso === 'biometrico'
                                                        ? 'Coloca la huella o ingresa el código del atleta'
                                                        : 'Acerca el carnet digital QR o digita el código de socio'}
                                            </p>
                                        </div>

                                        <form onSubmit={handleFormSubmit} className="mt-2 flex flex-col sm:flex-row gap-2">
                                            <div className="relative flex-1">
                                                <Search className="absolute left-3.5 top-3.5 h-4 w-4 text-slate-400 dark:text-slate-500" />
                                                <input
                                                    type="text"
                                                    value={codigoAcceso}
                                                    onChange={(e) => setCodigoAcceso(e.target.value)}
                                                    placeholder="Código de socio (ej. SOC-0001) o DNI..."
                                                    className="w-full min-h-[48px] rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/90 py-3 pl-10 pr-3 text-base sm:text-xs font-mono text-slate-950 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 shadow-inner focus:border-lime-500 dark:focus:border-lime-400 focus:outline-hidden focus:ring-1 focus:ring-lime-500"
                                                />
                                            </div>

                                            <button
                                                type="submit"
                                                disabled={procesandoAcceso}
                                                className={`w-full sm:w-auto min-h-[48px] inline-flex items-center justify-center gap-1.5 rounded-xl px-5 py-3 text-xs font-black uppercase tracking-wider transition disabled:opacity-50 cursor-pointer active:scale-98 ${
                                                    modoPaso === 'salida'
                                                        ? 'bg-gradient-to-r from-cyan-400 to-blue-500 text-slate-950 shadow-[0_0_20px_rgba(6,182,212,0.3)] hover:shadow-[0_0_25px_rgba(6,182,212,0.5)]'
                                                        : 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.3)] hover:shadow-[0_0_25px_rgba(132,204,22,0.5)]'
                                                }`}
                                            >
                                                {procesandoAcceso ? (
                                                    <RefreshCw className="h-4 w-4 animate-spin text-slate-950" />
                                                ) : modoPaso === 'salida' ? (
                                                    <LogOut className="h-4 w-4 text-slate-950" />
                                                ) : (
                                                    <Zap className="h-4 w-4 fill-slate-950 text-slate-950" />
                                                )}
                                                {modoPaso === 'salida' ? 'Marcar Salida' : 'Validar Entrada'}
                                            </button>
                                        </form>
                                    </div>

                                    {/* Botones de simulación rápida */}
                                    <div className="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                        <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                            Test Rápido:
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => ejecutarEscaneo('SOC-0001')}
                                            className="rounded-lg border border-lime-500/40 bg-lime-500/10 px-2.5 py-1 text-[11px] font-mono font-bold text-lime-700 dark:text-lime-400 hover:bg-lime-500/20 transition cursor-pointer"
                                        >
                                            SOC-0001 (Al Día)
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => ejecutarEscaneo('SOC-0002')}
                                            className="rounded-lg border border-amber-500/40 bg-amber-500/10 px-2.5 py-1 text-[11px] font-mono font-bold text-amber-700 dark:text-amber-400 hover:bg-amber-500/20 transition cursor-pointer"
                                        >
                                            SOC-0002 (Por Vencer)
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => ejecutarEscaneo('SOC-0003')}
                                            className="rounded-lg border border-rose-500/40 bg-rose-500/10 px-2.5 py-1 text-[11px] font-mono font-bold text-rose-700 dark:text-rose-400 hover:bg-rose-500/20 transition cursor-pointer"
                                        >
                                            SOC-0003 (Vencido)
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => ejecutarEscaneo('SOC-0004')}
                                            className="rounded-lg border border-red-500/40 bg-red-500/10 px-2.5 py-1 text-[11px] font-mono font-bold text-red-700 dark:text-red-400 hover:bg-red-500/20 transition cursor-pointer"
                                        >
                                            SOC-0004 (Moroso)
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => ejecutarEscaneo('SOC-0001', true)}
                                            className="rounded-lg border border-cyan-500/40 bg-cyan-500/10 px-2.5 py-1 text-[11px] font-mono font-bold text-cyan-700 dark:text-cyan-400 hover:bg-cyan-500/20 transition cursor-pointer"
                                        >
                                            Salida SOC-0001
                                        </button>
                                    </div>

                                    {/* RESULTADO DEL ESCANEO CON HARDWARE FEEDBACK Y CREDENCIAL DIGITAL 3D */}
                                    {resultadoAcceso && (
                                        <div className="mt-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-950/90 p-4 space-y-3 shadow-xs">
                                            {/* Banner de Estado del Torniquete */}
                                            {resultadoAcceso.tipo_evento === 'salida' ? (
                                                <div className="flex items-center justify-between rounded-xl border border-cyan-500/40 bg-cyan-500/10 dark:bg-cyan-950/30 p-3 text-cyan-800 dark:text-cyan-300">
                                                    <div className="flex items-center gap-2.5">
                                                        <DoorOpen className="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                                                        <div>
                                                            <div className="text-xs font-black uppercase tracking-wider text-cyan-700 dark:text-cyan-400">
                                                                Salida Registrada • Torniquete Abierto
                                                            </div>
                                                            <div className="text-xs text-slate-700 dark:text-slate-300">
                                                                {resultadoAcceso.mensaje}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {resultadoAcceso.minutos_permanencia !== undefined && (
                                                        <span className="rounded-lg bg-cyan-500/20 border border-cyan-500/30 px-2.5 py-1 font-mono text-xs font-bold text-cyan-800 dark:text-cyan-300">
                                                            {resultadoAcceso.minutos_permanencia} min
                                                        </span>
                                                    )}
                                                </div>
                                            ) : resultadoAcceso.exito ? (
                                                <div className="flex items-center justify-between rounded-xl border border-lime-500/40 bg-lime-500/10 dark:bg-lime-950/30 p-3 text-lime-900 dark:text-lime-300">
                                                    <div className="flex items-center gap-2.5">
                                                        <CheckCircle2 className="h-5 w-5 text-lime-600 dark:text-lime-400 shrink-0" />
                                                        <div>
                                                            <div className="text-xs font-black uppercase tracking-wider text-lime-700 dark:text-lime-400 flex items-center gap-2">
                                                                <span>Acceso Autorizado • Torniquete Abierto</span>
                                                                <span className="text-[10px] text-slate-500 dark:text-slate-400 font-mono">({temporizadorTorniquete}s)</span>
                                                            </div>
                                                            <div className="text-xs text-slate-700 dark:text-slate-300">
                                                                {resultadoAcceso.mensaje}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className="rounded-xl border border-rose-500/50 bg-rose-500/10 dark:bg-rose-950/40 p-3.5 space-y-2">
                                                    <div className="flex items-start gap-2.5">
                                                        <ShieldX className="h-5 w-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" />
                                                        <div className="flex-1">
                                                            <div className="text-xs font-black uppercase tracking-wider text-rose-700 dark:text-rose-400">
                                                                Acceso Denegado • Torniquete Bloqueado
                                                            </div>
                                                            <p className="text-xs text-rose-800 dark:text-rose-200 mt-0.5 font-medium">
                                                                {resultadoAcceso.mensaje}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    {/* Botón de Resolución Rápida si hay socio identificado */}
                                                    {resultadoAcceso.socio && (
                                                        <div className="pt-2 border-t border-rose-500/20 flex flex-wrap items-center justify-between gap-2">
                                                            <span className="text-[11px] text-slate-600 dark:text-slate-400">
                                                                Socio: <strong className="text-slate-900 dark:text-white">{resultadoAcceso.socio.nombre_completo || resultadoAcceso.socio.nombre}</strong> ({resultadoAcceso.socio.codigo})
                                                            </span>
                                                            <button
                                                                type="button"
                                                                onClick={() => setSocioSeleccionado(resultadoAcceso.socio)}
                                                                className="inline-flex items-center gap-1.5 rounded-lg bg-lime-500 hover:bg-lime-400 px-3 py-1 text-xs font-black text-slate-950 transition cursor-pointer shadow-md"
                                                            >
                                                                <CreditCard className="h-3.5 w-3.5" />
                                                                Regularizar / Cobrar Cuota
                                                            </button>
                                                        </div>
                                                    )}
                                                </div>
                                            )}

                                            {/* Credencial Digital 3D */}
                                            {resultadoAcceso.socio && (
                                                <div className="flex flex-col items-center pt-2">
                                                    <p className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1">
                                                        <Sparkles className="h-3 w-3 text-lime-600 dark:text-lime-400" />
                                                        Credencial Digital del Socio
                                                    </p>
                                                    <CredencialDigitalSocio
                                                        socio={{
                                                            ...resultadoAcceso.socio,
                                                            nombre:
                                                                resultadoAcceso.socio.nombre_completo?.split(' ')[0] ||
                                                                resultadoAcceso.socio.nombre,
                                                            apellido:
                                                                resultadoAcceso.socio.nombre_completo
                                                                    ?.split(' ')
                                                                    .slice(1)
                                                                    .join(' ') || resultadoAcceso.socio.apellido,
                                                        }}
                                                        esActiva={resultadoAcceso.exito}
                                                        onRenovar={() => setSocioSeleccionado(resultadoAcceso.socio)}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* CONTENIDO DE PESTAÑA 2: ATLETAS EN SALA EN VIVO */}
                            {tabAccesoActiva === 'en_sala' && (
                                <div className="mt-4 animate-in fade-in duration-200">
                                    <div className="mb-3 flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <h3 className="text-xs font-black text-slate-800 dark:text-slate-300 uppercase tracking-wider">
                                                Atletas Entrenando en Sala ({socios_en_sala.length})
                                            </h3>
                                        </div>
                                        <span className="text-[11px] text-slate-500 font-mono">
                                            Check-out rápido disponible
                                        </span>
                                    </div>

                                    {socios_en_sala.length === 0 ? (
                                        <div className="flex flex-col items-center justify-center p-8 text-center text-slate-500 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950/40">
                                            <Users className="h-8 w-8 text-slate-400 dark:text-slate-600 mb-2" />
                                            <p className="text-xs font-semibold text-slate-700 dark:text-slate-300">No hay atletas en sala en este momento</p>
                                            <span className="text-[11px] text-slate-500">Los socios con check-in abierto aparecerán aquí en vivo.</span>
                                        </div>
                                    ) : (
                                        <div className="space-y-2 max-h-[360px] overflow-y-auto pr-1">
                                            {socios_en_sala.map((item) => (
                                                <div
                                                    key={item.id}
                                                    className="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50/80 dark:bg-slate-900/60 p-3 transition hover:border-slate-300 dark:hover:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-900/90"
                                                >
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-lime-500/10 border border-lime-500/30 text-lime-700 dark:text-lime-400 font-bold text-xs">
                                                            {item.socio?.nombre?.charAt(0)}
                                                            {item.socio?.apellido?.charAt(0)}
                                                        </div>
                                                        <div>
                                                            <div className="flex items-center gap-2">
                                                                <span className="font-bold text-xs text-slate-950 dark:text-white">
                                                                    {item.socio?.nombre} {item.socio?.apellido}
                                                                </span>
                                                                <span className="font-mono text-[10px] text-slate-600 dark:text-slate-400 rounded bg-slate-200 dark:bg-slate-800 px-1.5 py-0.5">
                                                                    {item.socio?.codigo}
                                                                </span>
                                                            </div>
                                                            <div className="flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                                <span>Ingreso: {item.fecha_hora_entrada}</span>
                                                                <span>•</span>
                                                                <span className="text-lime-700 dark:text-lime-400 font-medium flex items-center gap-0.5">
                                                                    <Clock className="h-3 w-3" />
                                                                    hace {item.minutos_en_sala} min
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        disabled={procesandoSalidaId === item.id}
                                                        onClick={() => ejecutarCheckOutSocio(item.id, item.socio?.codigo)}
                                                        className="inline-flex items-center gap-1 rounded-lg border border-cyan-500/30 bg-cyan-500/10 px-2.5 py-1.5 text-xs font-bold text-cyan-700 dark:text-cyan-400 hover:bg-cyan-500/20 transition cursor-pointer disabled:opacity-50"
                                                    >
                                                        {procesandoSalidaId === item.id ? (
                                                            <RefreshCw className="h-3.5 w-3.5 animate-spin" />
                                                        ) : (
                                                            <LogOut className="h-3.5 w-3.5" />
                                                        )}
                                                        <span>Marcar Salida</span>
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* CONTENIDO DE PESTAÑA 3: HISTORIAL EN VIVO */}
                            {tabAccesoActiva === 'recientes' && (
                                <div className="mt-4 animate-in fade-in duration-200">
                                    <div className="mb-3 flex items-center justify-between">
                                        <h3 className="text-xs font-black text-slate-800 dark:text-slate-300 uppercase tracking-wider">
                                            Últimos Movimientos de Acceso Hoy
                                        </h3>
                                        <span className="text-[10px] text-slate-500 font-mono">
                                            Feed en tiempo real
                                        </span>
                                    </div>

                                    <div className="space-y-2 max-h-[360px] overflow-y-auto pr-1">
                                        {asistencias_recientes.length === 0 ? (
                                            <p className="text-center py-6 text-xs text-slate-500">No hay registros de acceso hoy.</p>
                                        ) : (
                                            asistencias_recientes.map((asistencia) => {
                                                const esPermitido = asistencia.estado_acceso === 'permitido';
                                                const esSalida = asistencia.estado_acceso === 'salida_registrada';

                                                return (
                                                    <div
                                                        key={asistencia.id}
                                                        className="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-800/60 bg-slate-50/80 dark:bg-slate-900/40 p-2.5 text-xs transition hover:bg-slate-100 dark:hover:bg-slate-900/80"
                                                    >
                                                        <div className="flex items-center gap-2.5">
                                                            <div className={`h-2 w-2 rounded-full shrink-0 ${
                                                                esSalida
                                                                    ? 'bg-cyan-500'
                                                                    : esPermitido
                                                                        ? 'bg-lime-500'
                                                                        : 'bg-rose-500'
                                                            }`} />
                                                            <div>
                                                                <span className="font-bold text-slate-950 dark:text-white">
                                                                    {asistencia.socio?.nombre} {asistencia.socio?.apellido}
                                                                </span>
                                                                <span className="font-mono text-[10px] text-slate-500 ml-1.5">
                                                                    {asistencia.socio?.codigo}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div className="flex items-center gap-2">
                                                            <span className="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                                                {asistencia.fecha_hora ? (asistencia.fecha_hora.includes('T') ? asistencia.fecha_hora.split('T')[1]?.substring(0, 5) : asistencia.fecha_hora.split(' ')[1]?.substring(0, 5) ?? asistencia.fecha_hora) : ''}
                                                            </span>
                                                            <span className={`rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ${
                                                                esSalida
                                                                    ? 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30'
                                                                    : esPermitido
                                                                        ? 'bg-lime-500/10 text-lime-700 dark:text-lime-400 border border-lime-500/30'
                                                                        : 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30'
                                                            }`}>
                                                                {esSalida ? 'Salida' : esPermitido ? 'Entrada' : 'Denegado'}
                                                            </span>
                                                        </div>
                                                    </div>
                                                );
                                            })
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* ALERTA DE MEMBRESÍAS VENCIDAS CON BADGES ANIMADOS */}
                    <div className="lg:col-span-5 rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/90 dark:to-slate-950 p-6 shadow-xl dark:shadow-2xl backdrop-blur-md">
                        <div className="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-4">
                            <div className="flex items-center gap-2.5">
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                                    <AlertTriangle className="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 className="text-base font-bold text-slate-950 dark:text-white uppercase tracking-wide">
                                        Auditoría de Vencimientos
                                    </h2>
                                    <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                        Socios con cuota vencida o con vencimiento en los próximos 7 días
                                    </p>
                                </div>
                            </div>

                            <span className="rounded-full border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                                {alertas_membresias.length} Casos
                            </span>
                        </div>

                        {/* Listado de Casos Críticos */}
                        <div className="mt-4 space-y-3">
                            {alertas_membresias.length === 0 ? (
                                <div className="flex flex-col items-center justify-center p-8 text-center text-slate-500">
                                    <ShieldCheck className="h-10 w-10 text-emerald-500 mb-2 opacity-80" />
                                    <p className="text-sm font-semibold text-slate-700 dark:text-slate-300">Todas las membresías al día</p>
                                    <span className="text-xs text-slate-500">No hay alertas de cobranza pendientes</span>
                                </div>
                            ) : (
                                alertas_membresias.map((alerta) => (
                                    <div
                                        key={alerta.id}
                                        className="group flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-800/70 bg-slate-50/80 dark:bg-slate-900/50 p-3.5 transition hover:border-slate-300 dark:hover:border-slate-700 hover:bg-slate-100/80 dark:hover:bg-slate-900/90"
                                    >
                                        <div className="flex items-center gap-3.5">
                                            <div
                                                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-bold text-xs uppercase shadow-md ${
                                                    alerta.esta_vencida
                                                        ? 'bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/40'
                                                        : 'bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/40'
                                                }`}
                                            >
                                                {alerta.socio?.nombre?.charAt(0)}
                                                {alerta.socio?.apellido?.charAt(0)}
                                            </div>

                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <span className="font-bold text-sm text-slate-950 dark:text-white">
                                                        {alerta.socio?.nombre} {alerta.socio?.apellido}
                                                    </span>
                                                    <span className="font-mono text-[10px] text-slate-600 dark:text-slate-400 rounded bg-slate-200 dark:bg-slate-800 px-1.5 py-0.5">
                                                        {alerta.socio?.codigo}
                                                    </span>
                                                </div>

                                                <div className="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                                    <span className="text-slate-700 dark:text-slate-300 font-medium">{alerta.plan}</span>
                                                    <span>•</span>
                                                    <span>Tel: {alerta.socio?.telefono || 'No reg.'}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t border-slate-200 dark:border-slate-800/60 sm:border-0">
                                            {/* Badge animado de alerta con ping */}
                                            <BadgeVencimiento
                                                diasRestantes={alerta.dias_restantes}
                                                fechaFin={alerta.fecha_fin}
                                                estaVencida={alerta.esta_vencida}
                                            />

                                            <button
                                                type="button"
                                                onClick={() => setSocioSeleccionado(alerta.socio)}
                                                className="inline-flex items-center gap-1.5 rounded-lg border border-lime-500 dark:border-lime-400/40 bg-lime-500/10 px-3 py-1.5 text-xs font-bold text-lime-700 dark:text-lime-400 transition hover:bg-lime-500 hover:text-slate-950 shadow-xs cursor-pointer"
                                            >
                                                <CreditCard className="h-3.5 w-3.5" />
                                                Renovar
                                            </button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </section>

                {/* 4. TABLA DE ASISTENCIAS EN TIEMPO REAL */}
                <section className="rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/90 dark:to-slate-950 shadow-xl dark:shadow-2xl backdrop-blur-md overflow-hidden">
                    <div className="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 p-5">
                        <div className="flex items-center gap-3">
                            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-600 dark:text-cyan-400">
                                <Clock className="h-5 w-5" />
                            </div>
                            <div>
                                <h2 className="text-base font-bold text-slate-950 dark:text-white uppercase tracking-wide">
                                    Últimos Accesos al Gimnasio
                                </h2>
                                <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                    Historial en vivo de validaciones en los torniquetes de entrada
                                </p>
                            </div>
                        </div>

                        <a
                            href="/asistencias"
                            className="inline-flex items-center gap-1 text-xs font-bold text-lime-700 dark:text-lime-400 hover:text-lime-800 dark:hover:text-lime-300 transition"
                        >
                            Ver Todo el Registro <ArrowUpRight className="h-4 w-4" />
                        </a>
                    </div>

                    {/* Versión Escritorio (md en adelante) */}
                    <div className="hidden md:block overflow-x-auto">
                        <table className="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                            <thead className="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                <tr>
                                    <th className="py-3 px-4">Hora</th>
                                    <th className="py-3 px-4">Código Socio</th>
                                    <th className="py-3 px-4">Socio / Miembro</th>
                                    <th className="py-3 px-4">DNI</th>
                                    <th className="py-3 px-4">Método</th>
                                    <th className="py-3 px-4">Estado de Acceso</th>
                                    <th className="py-3 px-4">Detalle en Pantalla</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                                {asistencias_recientes.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="py-8 text-center text-slate-500">
                                            No hay registros de asistencias en el día de hoy.
                                        </td>
                                    </tr>
                                ) : (
                                    asistencias_recientes.map((item) => {
                                        const permitido = item.estado_acceso === 'permitido';
                                        return (
                                            <tr
                                                key={item.id}
                                                className="transition hover:bg-slate-50 dark:hover:bg-slate-900/60"
                                            >
                                                <td className="py-3.5 px-4 font-mono text-xs text-slate-800 dark:text-slate-300 font-bold">
                                                    {new Date(item.fecha_hora).toLocaleTimeString('es-ES', {
                                                        hour: '2-digit',
                                                        minute: '2-digit',
                                                        second: '2-digit',
                                                    })}
                                                </td>
                                                <td className="py-3.5 px-4 font-mono text-xs font-bold text-lime-700 dark:text-lime-400">
                                                    {item.socio?.codigo || 'N/A'}
                                                </td>
                                                <td className="py-3.5 px-4 text-slate-950 dark:text-white font-bold">
                                                    <div className="flex items-center gap-2">
                                                        <div className="h-6 w-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] text-slate-700 dark:text-slate-300 font-bold">
                                                            {item.socio?.nombre?.charAt(0)}
                                                        </div>
                                                        <span>
                                                            {item.socio
                                                                ? `${item.socio.nombre} ${item.socio.apellido}`
                                                                : 'Socio No Encontrado'}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3.5 px-4 font-mono text-xs text-slate-500 dark:text-slate-400">
                                                    {item.socio?.dni || '-'}
                                                </td>
                                                <td className="py-3.5 px-4">
                                                    <span className="rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                                        {item.metodo}
                                                    </span>
                                                </td>
                                                <td className="py-3.5 px-4">
                                                    <span
                                                        className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-extrabold ${
                                                            permitido
                                                                ? 'bg-lime-500/15 text-lime-700 dark:text-lime-400 border border-lime-500/30'
                                                                : 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30'
                                                        }`}
                                                    >
                                                        {permitido ? (
                                                            <>
                                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                                                PERMITIDO
                                                            </>
                                                        ) : (
                                                            <>
                                                                <XCircle className="h-3.5 w-3.5" />
                                                                DENEGADO
                                                            </>
                                                        )}
                                                    </span>
                                                </td>
                                                <td className="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-400 max-w-sm truncate">
                                                    {item.mensaje_acceso || 'Sin observaciones'}
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Versión Móvil: Lista de Tarjetas de Acceso Reciente (block md:hidden) */}
                    <div className="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/80">
                        {asistencias_recientes.length === 0 ? (
                            <div className="py-8 text-center text-slate-500 text-sm">
                                No hay registros de asistencias en el día de hoy.
                            </div>
                        ) : (
                            asistencias_recientes.map((item) => {
                                const permitido = item.estado_acceso === 'permitido';
                                return (
                                    <div key={item.id} className="p-4 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <span
                                                className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-extrabold ${
                                                    permitido
                                                        ? 'bg-lime-500/15 text-lime-700 dark:text-lime-400 border border-lime-500/30'
                                                        : 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30'
                                                }`}
                                            >
                                                {permitido ? (
                                                    <>
                                                        <CheckCircle2 className="h-3 w-3" />
                                                        PERMITIDO
                                                    </>
                                                ) : (
                                                    <>
                                                        <XCircle className="h-3 w-3" />
                                                        DENEGADO
                                                    </>
                                                )}
                                            </span>

                                            <span className="font-mono text-xs font-bold text-slate-500 dark:text-slate-400">
                                                {new Date(item.fecha_hora).toLocaleTimeString('es-ES', {
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                    second: '2-digit',
                                                })}
                                            </span>
                                        </div>

                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <div className="h-7 w-7 rounded-lg bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-xs text-lime-700 dark:text-lime-400 font-black">
                                                    {item.socio?.nombre?.charAt(0) || 'S'}
                                                </div>
                                                <div>
                                                    <h4 className="font-bold text-sm text-slate-950 dark:text-white">
                                                        {item.socio ? `${item.socio.nombre} ${item.socio.apellido}` : 'Socio No Registrado'}
                                                    </h4>
                                                    <span className="font-mono text-[11px] text-lime-700 dark:text-lime-400">
                                                        {item.socio?.codigo || 'N/A'} • DNI: {item.socio?.dni || '-'}
                                                    </span>
                                                </div>
                                            </div>

                                            <span className="rounded bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 dark:text-slate-400">
                                                {item.metodo}
                                            </span>
                                        </div>

                                        {item.mensaje_acceso && (
                                            <p className="text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-950/70 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800/60">
                                                {item.mensaje_acceso}
                                            </p>
                                        )}
                                    </div>
                                );
                            })
                        )}
                    </div>
                </section>

                {/* MODAL DE RENOVACIÓN DE MEMBRESÍA */}
                {socioSeleccionado && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 p-3 sm:p-4 backdrop-blur-md">
                        <div className="w-[95%] max-w-md max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-2xl relative animate-in fade-in zoom-in-95 duration-200">
                            <button
                                type="button"
                                onClick={() => setSocioSeleccionado(null)}
                                className="absolute top-4 right-4 p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                            >
                                <X className="h-5 w-5" />
                            </button>

                            <div className="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-500/10 border border-lime-500/30 text-lime-600 dark:text-lime-400 shadow-[0_0_15px_rgba(132,204,22,0.3)]">
                                    <CreditCard className="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 className="text-base font-bold text-slate-950 dark:text-white uppercase tracking-wide">
                                        Renovación de Membresía
                                    </h3>
                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                        Actualización y activación de cuota inmediata
                                    </p>
                                </div>
                            </div>

                            <form onSubmit={handleRenovacion} className="mt-5 space-y-4">
                                <div className="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-3.5">
                                    <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                        Datos del Socio
                                    </span>
                                    <p className="text-sm font-bold text-slate-950 dark:text-white mt-0.5">
                                        {socioSeleccionado.nombre_completo ||
                                            `${socioSeleccionado.nombre} ${socioSeleccionado.apellido}`}
                                    </p>
                                    <p className="text-xs font-mono text-lime-700 dark:text-lime-400">
                                        Código: {socioSeleccionado.codigo} • DNI: {socioSeleccionado.dni}
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Selecciona el Plan
                                    </label>
                                    <select
                                        value={planSeleccionado}
                                        onChange={(e) => setPlanSeleccionado(Number(e.target.value))}
                                        className="w-full min-h-[44px] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 text-base sm:text-xs text-slate-950 dark:text-white focus:border-lime-500 focus:outline-hidden"
                                    >
                                        {planes.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nombre} — {formatCurrency(Number(p.precio))} ({p.duracion_dias} días)
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Método de Pago
                                    </label>
                                    <div className="grid grid-cols-3 gap-2">
                                        {['efectivo', 'tarjeta', 'transferencia'].map((metodo) => (
                                            <button
                                                key={metodo}
                                                type="button"
                                                onClick={() => setMetodoPago(metodo)}
                                                className={`min-h-[44px] rounded-xl border py-2.5 text-xs font-bold uppercase tracking-wider transition cursor-pointer active:scale-98 ${
                                                    metodoPago === metodo
                                                        ? 'border-lime-500 bg-lime-500/10 text-lime-700 dark:text-lime-400 shadow-[0_0_15px_rgba(132,204,22,0.2)]'
                                                        : 'border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:border-slate-400 dark:hover:border-slate-700'
                                                }`}
                                            >
                                                {metodo}
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                <div className="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                                    <button
                                        type="button"
                                        onClick={() => setSocioSeleccionado(null)}
                                        className="min-h-[44px] rounded-xl px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={procesandoRenovacion}
                                        className="min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-lime-400 to-emerald-500 px-5 py-2.5 text-xs font-black uppercase tracking-wider text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.3)] hover:shadow-[0_0_25px_rgba(132,204,22,0.5)] transition disabled:opacity-50 cursor-pointer active:scale-98"
                                    >
                                        {procesandoRenovacion ? 'Activando...' : 'Confirmar y Activar Cuota'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

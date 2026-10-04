import CredencialDigitalSocio from '@/components/credencial-digital-socio';
import { Head, Link, router } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    CreditCard,
    Dumbbell,
    Flame,
    LogOut,
    QrCode,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    UserCheck,
    Zap,
} from 'lucide-react';
import React, { useState } from 'react';

interface EjercicioPivot {
    orden: number;
    series: number;
    repeticiones: string;
    descanso_segundos: number;
    notas?: string;
}

interface EjercicioItem {
    id: number;
    nombre: string;
    grupo_muscular: string;
    equipo_necesario?: string;
    pivot?: EjercicioPivot;
}

interface RutinaItem {
    id: number;
    nombre: string;
    descripcion?: string;
    nivel: string;
    dias_semana?: string[];
    activa: boolean;
    ejercicios: EjercicioItem[];
}

interface AsistenciaItem {
    id: number;
    fecha_hora: string;
    metodo: string;
    estado_acceso: string;
    mensaje_acceso?: string;
}

interface SocioData {
    id: number;
    user_id: number;
    codigo: string;
    nombre: string;
    apellido: string;
    dni: string;
    correo: string;
    telefono?: string;
    foto?: string;
    estado: string;
    membresia_activa?: {
        id: number;
        plan?: { nombre: string; precio: number | string };
        fecha_fin: string;
        estado: string;
    };
    ultima_membresia?: {
        id: number;
        plan?: { nombre: string; precio: number | string };
        fecha_fin: string;
        estado: string;
    };
    rutinas: RutinaItem[];
    asistencias: AsistenciaItem[];
}

interface MiPerfilProps {
    socio: SocioData;
}

export default function MiPerfil({ socio }: MiPerfilProps) {
    const [seccionActiva, setSeccionActiva] = useState<'rutina' | 'asistencias'>('rutina');

    const membresiaActual = socio.membresia_activa || socio.ultima_membresia;
    const esActiva = socio.estado === 'activo' && Boolean(socio.membresia_activa);

    const handleLogout = () => {
        router.post(route('logout'));
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-lime-500 selection:text-slate-950">
            <Head title={`Mi Perfil • ${socio.nombre} ${socio.apellido} - FitLife`} />

            {/* HEADER SUPERIOR DEL PORTAL DE SOCIO */}
            <header className="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-lime-400 to-emerald-500 shadow-[0_0_15px_rgba(132,204,22,0.3)]">
                            <Dumbbell className="h-5 w-5 text-slate-950" />
                        </div>
                        <div>
                            <span className="text-lg font-black tracking-tight text-white uppercase italic">
                                FITLIFE <span className="text-lime-400 not-italic">PORTAL</span>
                            </span>
                            <span className="hidden sm:inline-block ml-2 rounded-full border border-lime-500/30 bg-lime-500/10 px-2 py-0.5 text-[10px] font-mono font-bold text-lime-400">
                                {socio.codigo}
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="text-right hidden sm:block">
                            <div className="text-xs font-bold text-white leading-tight">
                                {socio.nombre} {socio.apellido}
                            </div>
                            <div className="text-[10px] text-slate-400 font-mono">
                                DNI: {socio.dni}
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={handleLogout}
                            className="inline-flex items-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900/80 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:border-rose-500/40 hover:text-rose-400 transition cursor-pointer"
                        >
                            <LogOut className="h-3.5 w-3.5" />
                            <span>Cerrar Sesión</span>
                        </button>
                    </div>
                </div>
            </header>

            {/* CUERPO PRINCIPAL */}
            <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
                {/* Banner de bienvenida y estado */}
                <div className="mb-8 rounded-2xl border border-slate-800/80 bg-gradient-to-r from-slate-900/95 via-slate-900/80 to-slate-950 p-5 shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <h1 className="text-xl sm:text-2xl font-black text-white">
                                ¡Hola, {socio.nombre}!
                            </h1>
                            <span
                                className={`rounded-full px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider border ${
                                    esActiva
                                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                                        : 'bg-amber-500/10 text-amber-400 border-amber-500/30'
                                }`}
                            >
                                {esActiva ? 'Membresía Activa' : 'Pendiente de Activación'}
                            </span>
                        </div>
                        <p className="text-xs sm:text-sm text-slate-400">
                            {esActiva
                                ? 'Tu credencial digital está lista. Presenta tu código QR en el torniquete o recepción para ingresar.'
                                : 'Tu registro se ha completado. Acércate a recepción o realiza el pago para activar tu acceso a sala.'}
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 py-2 text-center">
                            <span className="text-[10px] text-slate-500 uppercase tracking-wider block">Código Socio</span>
                            <span className="text-sm font-mono font-bold text-lime-400">{socio.codigo}</span>
                        </div>
                    </div>
                </div>

                <div className="grid gap-8 lg:grid-cols-12">
                    {/* COLUMNA IZQUIERDA: CREDENCIAL DIGITAL 3D Y ESTADO DE MEMBRESÍA (5 cols) */}
                    <div className="lg:col-span-5 space-y-6">
                        {/* Credencial Digital 3D */}
                        <div className="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-5 shadow-xl backdrop-blur-md flex flex-col items-center">
                            <div className="w-full flex items-center justify-between mb-4 border-b border-slate-800/80 pb-3">
                                <span className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                    <Sparkles className="h-3.5 w-3.5 text-lime-400" />
                                    Tu Credencial Digital
                                </span>
                                <span className="text-[10px] text-slate-500 font-mono">Pasa el cursor para efecto 3D</span>
                            </div>

                            <CredencialDigitalSocio
                                socio={{
                                    ...socio,
                                    plan: membresiaActual?.plan?.nombre || 'Sin Plan Asignado',
                                    vence: membresiaActual?.fecha_fin,
                                }}
                                esActiva={esActiva}
                            />
                        </div>

                        {/* Tarjeta de Resumen de Membresía */}
                        <div className="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-5 shadow-xl">
                            <div className="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-4">
                                <span className="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                                    <CreditCard className="h-4 w-4 text-emerald-400" />
                                    Detalles de Membresía
                                </span>
                                {esActiva ? (
                                    <span className="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400">
                                        <ShieldCheck className="h-3.5 w-3.5" /> Al día
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1 text-[11px] font-bold text-amber-400">
                                        <ShieldAlert className="h-3.5 w-3.5" /> Por pagar
                                    </span>
                                )}
                            </div>

                            <div className="space-y-3 text-xs">
                                <div className="flex justify-between py-1 border-b border-slate-800/50">
                                    <span className="text-slate-400">Plan Seleccionado:</span>
                                    <span className="font-bold text-white">
                                        {membresiaActual?.plan?.nombre || 'Pase Estándar FitLife'}
                                    </span>
                                </div>
                                <div className="flex justify-between py-1 border-b border-slate-800/50">
                                    <span className="text-slate-400">Fecha de Vencimiento:</span>
                                    <span className="font-mono font-bold text-lime-400">
                                        {membresiaActual?.fecha_fin || 'Sin fecha asignada'}
                                    </span>
                                </div>
                                <div className="flex justify-between py-1">
                                    <span className="text-slate-400">Estado de Cuenta:</span>
                                    <span
                                        className={`font-bold ${
                                            esActiva ? 'text-emerald-400' : 'text-amber-400'
                                        }`}
                                    >
                                        {esActiva ? 'Activa y Habilitada' : 'Pendiente de Pago en Recepción'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* COLUMNA DERECHA: PESTAÑAS DE RUTINA ASIGNADA Y ASISTENCIAS (7 cols) */}
                    <div className="lg:col-span-7 space-y-6">
                        {/* Selector de Pestaña */}
                        <div className="flex items-center gap-2 border-b border-slate-800/80 pb-3">
                            <button
                                type="button"
                                onClick={() => setSeccionActiva('rutina')}
                                className={`flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition cursor-pointer ${
                                    seccionActiva === 'rutina'
                                        ? 'bg-lime-400/10 text-lime-400 border border-lime-400/30 shadow-[0_0_15px_rgba(132,204,22,0.2)]'
                                        : 'text-slate-400 hover:text-white hover:bg-slate-900'
                                }`}
                            >
                                <Dumbbell className="h-4 w-4" />
                                <span>Mi Plan de Entrenamiento ({socio.rutinas.length})</span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setSeccionActiva('asistencias')}
                                className={`flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition cursor-pointer ${
                                    seccionActiva === 'asistencias'
                                        ? 'bg-cyan-400/10 text-cyan-400 border border-cyan-400/30 shadow-[0_0_15px_rgba(6,182,212,0.2)]'
                                        : 'text-slate-400 hover:text-white hover:bg-slate-900'
                                }`}
                            >
                                <Clock className="h-4 w-4" />
                                <span>Historial de Asistencias ({socio.asistencias.length})</span>
                            </button>
                        </div>

                        {/* SECCIÓN 1: RUTINAS DE ENTRENAMIENTO */}
                        {seccionActiva === 'rutina' && (
                            <div className="space-y-4">
                                {socio.rutinas.length === 0 ? (
                                    <div className="rounded-2xl border border-dashed border-slate-800 p-8 text-center text-slate-500">
                                        <Dumbbell className="h-10 w-10 text-slate-600 mx-auto mb-2 opacity-50" />
                                        <p className="text-sm font-semibold text-slate-400">
                                            Aún no tienes una rutina prescrita.
                                        </p>
                                        <span className="text-xs text-slate-600">
                                            Tu entrenador te asignará una rutina personalizada adaptada a tu nivel y objetivo.
                                        </span>
                                    </div>
                                ) : (
                                    socio.rutinas.map((rutina) => (
                                        <div
                                            key={rutina.id}
                                            className="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-5 shadow-xl"
                                        >
                                            <div className="flex items-start justify-between mb-3">
                                                <div>
                                                    <span className="rounded-md bg-lime-400/10 border border-lime-400/20 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-lime-400">
                                                        Nivel {rutina.nivel}
                                                    </span>
                                                    <h2 className="text-base font-black text-white mt-1">
                                                        {rutina.nombre}
                                                    </h2>
                                                    {rutina.descripcion && (
                                                        <p className="text-xs text-slate-400 mt-0.5">
                                                            {rutina.descripcion}
                                                        </p>
                                                    )}
                                                </div>

                                                {rutina.dias_semana && rutina.dias_semana.length > 0 && (
                                                    <div className="flex flex-wrap gap-1">
                                                        {rutina.dias_semana.map((dia) => (
                                                            <span
                                                                key={dia}
                                                                className="rounded-md bg-slate-800 px-1.5 py-0.5 text-[9px] font-mono text-slate-300"
                                                            >
                                                                {dia}
                                                            </span>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>

                                            {/* Ejercicios de la Rutina */}
                                            <div className="mt-4 divide-y divide-slate-800/60">
                                                {rutina.ejercicios.map((ejercicio, idx) => (
                                                    <div
                                                        key={ejercicio.id}
                                                        className="py-2.5 flex items-center justify-between"
                                                    >
                                                        <div className="flex items-center gap-3">
                                                            <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-slate-800 text-[10px] font-mono font-bold text-lime-400">
                                                                {ejercicio.pivot?.orden || idx + 1}
                                                            </span>
                                                            <div>
                                                                <h3 className="text-xs font-bold text-white">
                                                                    {ejercicio.nombre}
                                                                </h3>
                                                                <span className="text-[10px] text-slate-400 uppercase">
                                                                    {ejercicio.grupo_muscular}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div className="flex items-center gap-4 text-xs font-mono">
                                                            {ejercicio.pivot && (
                                                                <>
                                                                    <div className="text-right">
                                                                        <span className="text-[10px] text-slate-500 block">Series x Reps</span>
                                                                        <span className="font-bold text-white">
                                                                            {ejercicio.pivot.series} × {ejercicio.pivot.repeticiones}
                                                                        </span>
                                                                    </div>
                                                                    <div className="text-right">
                                                                        <span className="text-[10px] text-slate-500 block">Descanso</span>
                                                                        <span className="font-bold text-lime-400">
                                                                            {ejercicio.pivot.descanso_segundos}s
                                                                        </span>
                                                                    </div>
                                                                </>
                                                            )}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        )}

                        {/* SECCIÓN 2: HISTORIAL DE ASISTENCIAS */}
                        {seccionActiva === 'asistencias' && (
                            <div className="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-5 shadow-xl">
                                <h2 className="text-sm font-bold uppercase tracking-wider text-white mb-4 flex items-center gap-2">
                                    <Clock className="h-4 w-4 text-cyan-400" />
                                    Últimos Ingresos al Gimnasio
                                </h2>

                                {socio.asistencias.length === 0 ? (
                                    <div className="p-8 text-center text-slate-500">
                                        <QrCode className="h-10 w-10 text-slate-600 mx-auto mb-2 opacity-50" />
                                        <p className="text-sm font-semibold text-slate-400">
                                            Aún no registras asistencias.
                                        </p>
                                        <span className="text-xs text-slate-600">
                                            Al escanear tu código QR en el torniquete aparecerán aquí tus ingresos.
                                        </span>
                                    </div>
                                ) : (
                                    <div className="divide-y divide-slate-800/60">
                                        {socio.asistencias.map((asistencia) => (
                                            <div
                                                key={asistencia.id}
                                                className="py-3 flex items-center justify-between"
                                            >
                                                <div className="flex items-center gap-3">
                                                    <div
                                                        className={`flex h-8 w-8 items-center justify-center rounded-lg ${
                                                            asistencia.estado_acceso === 'permitido'
                                                                ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                                                                : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                                                        }`}
                                                    >
                                                        {asistencia.estado_acceso === 'permitido' ? (
                                                            <CheckCircle2 className="h-4 w-4" />
                                                        ) : (
                                                            <AlertCircle className="h-4 w-4" />
                                                        )}
                                                    </div>
                                                    <div>
                                                        <div className="text-xs font-bold text-white">
                                                            {asistencia.mensaje_acceso || (asistencia.estado_acceso === 'permitido' ? 'Acceso Permitido' : 'Acceso Denegado')}
                                                        </div>
                                                        <span className="text-[10px] text-slate-400 font-mono uppercase">
                                                            Método: {asistencia.metodo}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div className="text-right font-mono text-xs text-slate-300">
                                                    {new Date(asistencia.fecha_hora).toLocaleDateString('es-ES', {
                                                        day: '2-digit',
                                                        month: 'short',
                                                        year: 'numeric',
                                                        hour: '2-digit',
                                                        minute: '2-digit',
                                                    })}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </main>
        </div>
    );
}


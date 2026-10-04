import DrawerAsignarRutina from '@/components/drawer-asignar-rutina';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Clock,
    Dumbbell,
    Edit3,
    Flame,
    Layers,
    Repeat,
    Shield,
    Trash2,
    User,
    UserCheck,
} from 'lucide-react';
import React, { useState } from 'react';

interface EjercicioPivot {
    orden: number;
    series: number;
    repeticiones: string;
    descanso_segundos: number;
    rpe?: number;
    tempo?: string;
    notas?: string;
}

interface EjercicioItem {
    id: number;
    nombre: string;
    grupo_muscular: string;
    descripcion?: string;
    equipo_necesario?: string;
    pivot?: EjercicioPivot;
}

interface RutinaDetalle {
    id: number;
    nombre: string;
    descripcion?: string;
    nivel: string;
    dias_semana?: string[];
    activa: boolean;
    created_at: string;
    socio?: {
        id: number;
        nombre: string;
        apellido: string;
        codigo: string;
        dni: string;
        estado: string;
    };
    creador?: {
        name: string;
    };
    ejercicios: EjercicioItem[];
}

interface RutinaShowProps {
    rutina: RutinaDetalle;
    ejercicios: EjercicioItem[];
    socios: Array<{ id: number; nombre: string; apellido: string; codigo: string }>;
}

export default function RutinaShow({ rutina, ejercicios = [], socios = [] }: RutinaShowProps) {
    const [drawerEditarAbierto, setDrawerEditarAbierto] = useState(false);
    const [guardando, setGuardando] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Rutinas', href: '/rutinas' },
        { title: rutina.nombre, href: `/rutinas/${rutina.id}` },
    ];

    const handleActualizarRutina = (datos: any) => {
        setGuardando(true);
        router.put(route('rutinas.update', rutina.id), datos, {
            onSuccess: () => {
                setGuardando(false);
                setDrawerEditarAbierto(false);
            },
            onError: () => {
                setGuardando(false);
            },
        });
    };

    const handleEliminarRutina = () => {
        if (confirm(`¿Estás seguro de eliminar permanentemente la rutina "${rutina.nombre}"?`)) {
            router.delete(route('rutinas.destroy', rutina.id));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Rutina: ${rutina.nombre} • FitLife Pro`} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-8 bg-slate-950 text-slate-100 min-h-full font-sans">
                {/* Barra de Navegación y Acciones Rápidas */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-800/80 pb-5">
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('rutinas.index')}
                            className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition"
                            title="Volver al catálogo"
                        >
                            <ArrowLeft className="h-5 w-5" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2.5">
                                <span
                                    className={`rounded-md px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider border ${
                                        rutina.nivel === 'avanzado'
                                            ? 'bg-rose-500/10 text-rose-400 border-rose-500/30'
                                            : rutina.nivel === 'intermedio'
                                              ? 'bg-amber-500/10 text-amber-400 border-amber-500/30'
                                              : 'bg-lime-400/10 text-lime-400 border-lime-400/30'
                                    }`}
                                >
                                    Nivel {rutina.nivel}
                                </span>
                                <span className="rounded-md bg-slate-800/80 px-2 py-0.5 text-[10px] font-mono text-slate-400">
                                    ID #{rutina.id}
                                </span>
                            </div>
                            <h1 className="text-2xl font-black tracking-tight text-white uppercase italic mt-1">
                                {rutina.nombre}
                            </h1>
                        </div>
                    </div>

                    <div className="flex items-center gap-2.5">
                        <button
                            type="button"
                            onClick={() => setDrawerEditarAbierto(true)}
                            className="inline-flex items-center gap-2 rounded-xl border border-lime-500/40 bg-lime-500/10 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-lime-400 shadow-[0_0_15px_rgba(132,204,22,0.25)] hover:bg-lime-500/20 hover:border-lime-400 transition cursor-pointer"
                        >
                            <Edit3 className="h-4 w-4" />
                            Editar Rutina
                        </button>

                        <button
                            type="button"
                            onClick={handleEliminarRutina}
                            className="inline-flex items-center gap-2 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-rose-400 hover:bg-rose-500/20 hover:border-rose-400 transition cursor-pointer"
                        >
                            <Trash2 className="h-4 w-4" />
                            Eliminar
                        </button>
                    </div>
                </div>

                {/* Banner de Socio Asignado o Plantilla */}
                <div className="rounded-2xl border border-slate-800/90 bg-gradient-to-r from-slate-900 to-slate-950 p-5 shadow-lg">
                    {rutina.socio ? (
                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div className="flex items-center gap-3.5">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 font-black text-base">
                                    <User className="h-6 w-6" />
                                </div>
                                <div>
                                    <div className="flex items-center gap-2">
                                        <h3 className="text-base font-bold text-white">
                                            {rutina.socio.nombre} {rutina.socio.apellido}
                                        </h3>
                                        <span
                                            className={`rounded-md px-2 py-0.5 text-[10px] font-bold uppercase ${
                                                rutina.socio.estado === 'activo'
                                                    ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                                                    : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                                            }`}
                                        >
                                            {rutina.socio.estado}
                                        </span>
                                    </div>
                                    <p className="text-xs text-slate-400 font-mono mt-0.5">
                                        Código: <strong className="text-cyan-400">{rutina.socio.codigo}</strong> • DNI: {rutina.socio.dni}
                                    </p>
                                </div>
                            </div>

                            <Link
                                href={route('socios.show', rutina.socio.id)}
                                className="inline-flex items-center gap-2 rounded-xl bg-cyan-500/10 border border-cyan-500/40 px-4 py-2 text-xs font-bold text-cyan-300 hover:bg-cyan-500/20 transition self-start sm:self-auto"
                            >
                                <UserCheck className="h-4 w-4" />
                                Ver Expediente Completo del Socio →
                            </Link>
                        </div>
                    ) : (
                        <div className="flex items-center gap-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 text-slate-400 border border-slate-700">
                                <Layers className="h-5 w-5" />
                            </div>
                            <div>
                                <h3 className="text-sm font-bold text-white">Plantilla General de Entrenamiento</h3>
                                <p className="text-xs text-slate-400">
                                    Esta rutina no está restringida a un socio individual y puede ser clonada o prescrita a cualquier miembro.
                                </p>
                            </div>
                        </div>
                    )}

                    {rutina.descripcion && (
                        <div className="mt-4 pt-3.5 border-t border-slate-800/80 text-xs text-slate-300 leading-relaxed">
                            {rutina.descripcion}
                        </div>
                    )}

                    {/* Metadata de prescripción y días */}
                    <div className="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500 pt-3 border-t border-slate-800/60 font-mono">
                        <div>
                            Prescrita por: <strong className="text-slate-300">{rutina.creador?.name || 'Coach del Gimnasio'}</strong>
                        </div>
                        {rutina.dias_semana && rutina.dias_semana.length > 0 && (
                            <div className="flex items-center gap-1.5">
                                <span className="text-slate-400">Días:</span>
                                {rutina.dias_semana.map((dia) => (
                                    <span
                                        key={dia}
                                        className="rounded bg-slate-800 border border-slate-700 px-2 py-0.5 text-[10px] font-bold text-lime-400"
                                    >
                                        {dia}
                                    </span>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Lista Detallada de Ejercicios */}
                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-sm font-black uppercase tracking-wider text-slate-300 flex items-center gap-2">
                            <Layers className="h-4 w-4 text-lime-400" />
                            Ejercicios de la Rutina ({rutina.ejercicios?.length || 0})
                        </h2>
                        <span className="text-xs text-slate-500 font-mono">
                            Series • Repeticiones • Descanso
                        </span>
                    </div>

                    {rutina.ejercicios?.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-slate-800 p-8 text-center text-slate-500">
                            No hay ejercicios asociados a esta rutina actualmente.
                        </div>
                    ) : (
                        <div className="grid gap-3">
                            {rutina.ejercicios?.map((ej, index) => (
                                <div
                                    key={ej.id}
                                    className="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 hover:border-lime-500/40 transition"
                                >
                                    <div className="flex items-start gap-3.5">
                                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-lime-400/10 border border-lime-400/30 font-black text-sm text-lime-400">
                                            {ej.pivot?.orden || index + 1}
                                        </div>
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <h4 className="text-base font-bold text-white">
                                                    {ej.nombre}
                                                </h4>
                                                <span className="rounded bg-slate-800 px-2 py-0.5 text-[10px] uppercase font-mono text-slate-400">
                                                    {ej.grupo_muscular}
                                                </span>
                                            </div>
                                            {ej.descripcion && (
                                                <p className="text-xs text-slate-400 mt-1">
                                                    {ej.descripcion}
                                                </p>
                                            )}
                                            {ej.pivot?.notas && (
                                                <p className="text-xs text-lime-400/90 bg-lime-400/5 border border-lime-400/20 rounded-lg p-2 mt-2">
                                                    💡 Indicación: {ej.pivot.notas}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    {/* Métricas pivote: Series, Reps, Descanso */}
                                    <div className="flex flex-wrap items-center gap-3 font-mono shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-800">
                                        <div className="rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2 text-center min-w-[70px]">
                                            <span className="text-[10px] uppercase text-slate-500 block">Series</span>
                                            <span className="text-base font-black text-lime-400">
                                                {ej.pivot?.series || 3}
                                            </span>
                                        </div>

                                        <div className="rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2 text-center min-w-[70px]">
                                            <span className="text-[10px] uppercase text-slate-500 block">Reps</span>
                                            <span className="text-base font-black text-white">
                                                {ej.pivot?.repeticiones || '10-12'}
                                            </span>
                                        </div>

                                        <div className="rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2 text-center min-w-[80px]">
                                            <span className="text-[10px] uppercase text-slate-500 block">Descanso</span>
                                            <span className="text-base font-black text-cyan-400 flex items-center justify-center gap-1">
                                                <Clock className="h-3 w-3" />
                                                {ej.pivot?.descanso_segundos || 60}s
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Drawer para Editar */}
                <DrawerAsignarRutina
                    abierto={drawerEditarAbierto}
                    onClose={() => setDrawerEditarAbierto(false)}
                    ejercicios={ejercicios}
                    socios={socios}
                    rutinaParaEditar={rutina}
                    onGuardar={handleActualizarRutina}
                    guardando={guardando}
                />
            </div>
        </AppLayout>
    );
}


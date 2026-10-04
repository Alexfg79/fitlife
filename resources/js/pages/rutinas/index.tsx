import DrawerAsignarRutina from '@/components/drawer-asignar-rutina';
import PaginadorCompacto, { type PaginatorMeta } from '@/components/paginador-compacto';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    Clock,
    Dumbbell,
    Edit3,
    Eye,
    Flame,
    Layers,
    LoaderCircle,
    Plus,
    Repeat,
    Sparkles,
    Trash2,
    UserCheck,
    Users,
} from 'lucide-react';
import React, { useState } from 'react';

interface EjercicioItem {
    id: number;
    nombre: string;
    grupo_muscular: string;
    descripcion?: string;
    equipo_necesario?: string;
    pivot?: {
        orden: number;
        series: number;
        repeticiones: string;
        descanso_segundos: number;
        notas?: string;
    };
}

interface RutinaItem {
    id: number;
    nombre: string;
    descripcion?: string;
    nivel: string;
    dias_semana?: string[];
    activa: boolean;
    socio?: {
        id: number;
        nombre: string;
        apellido: string;
        codigo: string;
    };
    creador?: {
        name: string;
    };
    ejercicios: EjercicioItem[];
}

interface RutinasProps {
    rutinas: PaginatorMeta & {
        data: RutinaItem[];
    };
    ejercicios: EjercicioItem[];
    socios: Array<{ id: number; nombre: string; apellido: string; codigo: string }>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Rutinas y Ejercicios', href: '/rutinas' },
];

export default function RutinasIndex({ rutinas, ejercicios = [], socios = [] }: RutinasProps) {
    const [drawerAbierto, setDrawerAbierto] = useState(false);
    const [rutinaParaEditar, setRutinaParaEditar] = useState<RutinaItem | null>(null);
    const [guardando, setGuardando] = useState(false);
    const [generandoAuto, setGenerandoAuto] = useState(false);

    const handleRutinaAutomatica = () => {
        setGenerandoAuto(true);
        router.post(
            route('rutinas.generar-automatica'),
            {},
            {
                preserveScroll: true,
                onFinish: () => setGenerandoAuto(false),
            }
        );
    };

    const handleCrearNueva = () => {
        setRutinaParaEditar(null);
        setDrawerAbierto(true);
    };

    const handleEditar = (rutina: RutinaItem) => {
        setRutinaParaEditar(rutina);
        setDrawerAbierto(true);
    };

    const handleEliminarRutina = (id: number, nombre: string) => {
        if (confirm(`¿Estás seguro de eliminar la rutina "${nombre}"?`)) {
            router.delete(route('rutinas.destroy', id));
        }
    };

    const handleGuardarRutina = (datos: any) => {
        setGuardando(true);
        if (datos.id) {
            router.put(route('rutinas.update', datos.id), datos, {
                onSuccess: () => {
                    setGuardando(false);
                    setDrawerAbierto(false);
                    setRutinaParaEditar(null);
                },
                onError: () => {
                    setGuardando(false);
                },
            });
        } else {
            router.post(route('rutinas.store'), datos, {
                onSuccess: () => {
                    setGuardando(false);
                    setDrawerAbierto(false);
                },
                onError: () => {
                    setGuardando(false);
                },
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Planes de Entrenamiento • FitLife Pro" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-8 bg-slate-950 text-slate-100 min-h-full font-sans">
                {/* Cabecera */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-800/80 pb-6">
                    <div>
                        <div className="flex items-center gap-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-400/10 border border-lime-400/30 text-lime-400 shadow-[0_0_15px_rgba(132,204,22,0.3)]">
                                <Dumbbell className="h-5 w-5" />
                            </div>
                            <h1 className="text-2xl font-black tracking-tight text-white uppercase italic">
                                Planes de Entrenamiento & <span className="text-lime-400 not-italic">Rutinas</span>
                            </h1>
                        </div>
                        <p className="text-xs text-slate-400 mt-1">
                            Prescripción técnica de ejercicios, series, repeticiones y asignación individualizada para socios.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            disabled={generandoAuto}
                            onClick={handleRutinaAutomatica}
                            className="inline-flex items-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-500/10 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.25)] hover:bg-cyan-500/20 hover:border-cyan-400 transition cursor-pointer disabled:opacity-50"
                            title="Genera automáticamente una rutina personalizada a un socio usando plantillas preconfiguradas"
                        >
                            {generandoAuto ? (
                                <LoaderCircle className="h-4 w-4 animate-spin text-cyan-400" />
                            ) : (
                                <Sparkles className="h-4 w-4 text-cyan-400" />
                            )}
                            <span>{generandoAuto ? 'Generando...' : '⚡ Rutina Automática'}</span>
                        </button>

                        <button
                            onClick={handleCrearNueva}
                            className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-lime-400 to-emerald-500 px-5 py-2.5 text-xs font-black uppercase tracking-wider text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.35)] hover:shadow-[0_0_30px_rgba(132,204,22,0.55)] transition cursor-pointer"
                        >
                            <Plus className="h-4 w-4 stroke-[3]" />
                            Crear Nueva Rutina
                        </button>
                    </div>
                </div>

                {/* Listado de Rutinas con Estética Dark Mode Deportiva */}
                <div className="grid gap-6 md:grid-cols-2">
                    {rutinas.data.length === 0 ? (
                        <div className="col-span-2 rounded-2xl border border-dashed border-slate-800 p-12 text-center text-slate-500">
                            <Dumbbell className="h-10 w-10 text-slate-600 mx-auto mb-3 opacity-60" />
                            <p className="text-sm font-semibold text-slate-400">No hay rutinas creadas todavía.</p>
                            <span className="text-xs text-slate-600">Presiona "Crear Nueva Rutina" para diseñar la primera.</span>
                        </div>
                    ) : (
                        rutinas.data.map((rutina) => (
                            <div
                                key={rutina.id}
                                className="group relative rounded-2xl border border-slate-800/90 bg-gradient-to-b from-slate-900/95 to-slate-950 p-6 shadow-xl transition-all duration-300 hover:border-lime-500/40 hover:shadow-[0_0_30px_rgba(132,204,22,0.12)] flex flex-col justify-between"
                            >
                                <div>
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <span
                                                className={`inline-block rounded-md px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider mb-2 border ${
                                                    rutina.nivel === 'avanzado'
                                                        ? 'bg-rose-500/10 text-rose-400 border-rose-500/30'
                                                        : rutina.nivel === 'intermedio'
                                                          ? 'bg-amber-500/10 text-amber-400 border-amber-500/30'
                                                          : 'bg-lime-400/10 text-lime-400 border-lime-400/30'
                                                }`}
                                            >
                                                Nivel {rutina.nivel}
                                            </span>
                                            <h3 className="text-lg font-black text-white tracking-tight">
                                                <Link
                                                    href={route('rutinas.show', rutina.id)}
                                                    className="hover:text-lime-400 transition"
                                                >
                                                    {rutina.nombre}
                                                </Link>
                                            </h3>
                                        </div>

                                        {rutina.socio ? (
                                            <Link
                                                href={route('socios.show', rutina.socio.id)}
                                                className="rounded-full bg-cyan-500/10 border border-cyan-500/30 px-3 py-1 text-xs font-bold text-cyan-300 flex items-center gap-1.5 hover:bg-cyan-500/20 hover:border-cyan-400 transition cursor-pointer"
                                                title="Indagar expediente del socio"
                                            >
                                                <UserCheck className="h-3.5 w-3.5" />
                                                {rutina.socio.nombre} {rutina.socio.apellido}
                                            </Link>
                                        ) : (
                                            <span className="rounded-full bg-slate-800 border border-slate-700 px-3 py-1 text-xs font-medium text-slate-400">
                                                Plantilla General
                                            </span>
                                        )}
                                    </div>

                                    {rutina.descripcion && (
                                        <p className="mt-2.5 text-xs text-slate-400 leading-relaxed">
                                            {rutina.descripcion}
                                        </p>
                                    )}

                                    {/* Lista de Ejercicios */}
                                    <div className="mt-5 border-t border-slate-800/80 pt-4">
                                        <h4 className="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                            <Layers className="h-3.5 w-3.5 text-lime-400" />
                                            Ejercicios Prescritos ({rutina.ejercicios?.length || 0})
                                        </h4>
                                        <div className="space-y-2">
                                            {rutina.ejercicios?.map((ej, index) => (
                                                <div
                                                    key={ej.id}
                                                    className="flex items-center justify-between rounded-xl bg-slate-950/70 border border-slate-800/60 p-3 text-xs"
                                                >
                                                    <div className="flex items-center gap-2.5">
                                                        <span className="flex h-5 w-5 items-center justify-center rounded-lg bg-lime-400/10 border border-lime-400/30 text-[10px] font-black text-lime-400">
                                                            {index + 1}
                                                        </span>
                                                        <div>
                                                            <span className="font-bold text-white">
                                                                {ej.nombre}
                                                            </span>
                                                            <span className="ml-2 text-[10px] uppercase text-slate-500 font-mono">
                                                                [{ej.grupo_muscular}]
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div className="flex items-center gap-2.5 font-mono text-[11px] text-slate-300">
                                                        <span className="rounded bg-slate-800/80 px-2 py-0.5 text-lime-400 font-bold">
                                                            {ej.pivot?.series || 3}S
                                                        </span>
                                                        <span className="rounded bg-slate-800/80 px-2 py-0.5">
                                                            {ej.pivot?.repeticiones || '10'}R
                                                        </span>
                                                        <span className="text-slate-400">
                                                            ⏱ {ej.pivot?.descanso_segundos || 60}s
                                                        </span>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-5 pt-3.5 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-500">
                                    <div className="flex items-center gap-3">
                                        <span>Prescrita por: <strong className="text-slate-400">{rutina.creador?.name || 'Coach'}</strong></span>
                                        {rutina.dias_semana && rutina.dias_semana.length > 0 && (
                                            <div className="flex gap-1.5">
                                                {rutina.dias_semana.map((dia) => (
                                                    <span
                                                        key={dia}
                                                        className="rounded bg-slate-800/80 border border-slate-700/60 px-2 py-0.5 text-[10px] font-mono font-bold text-slate-300"
                                                    >
                                                        {dia}
                                                    </span>
                                                ))}
                                            </div>
                                        )}
                                    </div>

                                    {/* Botones de Acción CRUD */}
                                    <div className="flex items-center gap-1.5 self-end sm:self-auto">
                                        <Link
                                            href={route('rutinas.show', rutina.id)}
                                            className="rounded-lg bg-slate-800 p-2 text-slate-300 hover:bg-slate-700 hover:text-white transition"
                                            title="Ver Detalle"
                                        >
                                            <Eye className="h-3.5 w-3.5" />
                                        </Link>
                                        <button
                                            type="button"
                                            onClick={() => handleEditar(rutina)}
                                            className="rounded-lg bg-lime-500/10 border border-lime-500/30 p-2 text-lime-400 hover:bg-lime-500/20 transition cursor-pointer"
                                            title="Editar Rutina"
                                        >
                                            <Edit3 className="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => handleEliminarRutina(rutina.id, rutina.nombre)}
                                            className="rounded-lg bg-rose-500/10 border border-rose-500/30 p-2 text-rose-400 hover:bg-rose-500/20 transition cursor-pointer"
                                            title="Eliminar Rutina"
                                        >
                                            <Trash2 className="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* Barra de Navegación Paginada Compacta (1-50 de 5.042 < >) */}
                <div className="rounded-2xl border border-slate-800/80 bg-slate-900/60 px-5 py-3 flex items-center justify-end">
                    <PaginadorCompacto paginacion={rutinas} variant="dark" />
                </div>

                {/* Drawer Lateral Slide-Over */}
                <DrawerAsignarRutina
                    abierto={drawerAbierto}
                    onClose={() => {
                        setDrawerAbierto(false);
                        setRutinaParaEditar(null);
                    }}
                    ejercicios={ejercicios}
                    socios={socios}
                    rutinaParaEditar={rutinaParaEditar}
                    onGuardar={handleGuardarRutina}
                    guardando={guardando}
                />
            </div>
        </AppLayout>
    );
}

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    Calendar,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock,
    DollarSign,
    Dumbbell,
    Edit3,
    Flame,
    HeartPulse,
    MapPin,
    Plus,
    Sparkles,
    Trash2,
    User,
    UserCheck,
    Users,
    X,
    XCircle,
} from 'lucide-react';
import React, { useState } from 'react';

interface SocioItem {
    id: number;
    nombre_completo: string;
    dni: string;
    codigo: string;
    plan_nombre: string;
    es_basico: boolean;
    es_completo: boolean;
    es_premium: boolean;
}

interface InscripcionItem {
    id: number;
    socio_id: number;
    socio_nombre: string;
    socio_dni: string;
    socio_codigo: string;
    fecha_clase: string;
    monto_recargo: number;
    estado_pago: string;
    asistencia_confirmada: boolean;
    estado: string;
}

interface ClaseItem {
    id: number;
    nombre: string;
    descripcion?: string;
    dia_semana: string;
    hora_inicio: string;
    horario: string;
    duracion_minutos: number;
    cupo_maximo: number;
    cupos_restantes: number;
    esta_llena: boolean;
    sala?: string;
    activa: boolean;
    estado: string;
    sucursal?: {
        id: number;
        nombre: string;
        ciudad: string;
    } | null;
    profesor?: {
        id: number;
        nombre_completo: string;
        especialidad: string;
        telefono?: string;
        correo?: string;
    } | null;
    inscripciones: InscripcionItem[];
}

interface SucursalItem {
    id: number;
    nombre: string;
    ciudad: string;
}

interface ProfesorItem {
    id: number;
    nombre_completo: string;
    especialidad: string;
}

interface ClasesProps {
    clases: ClaseItem[];
    sucursales: SucursalItem[];
    profesores: ProfesorItem[];
    socios: SocioItem[];
    disciplinas: string[];
    diasSemana: string[];
    fechaSeleccionada: string;
    filtros: {
        disciplina?: string;
        sucursal_id?: number | null;
        dia_semana?: string;
        fecha?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clases Grupales', href: '/clases' },
];

export default function ClasesIndex({
    clases = [],
    sucursales = [],
    profesores = [],
    socios = [],
    disciplinas = ['Spinning', 'Yoga', 'Pilates', 'Zumba', 'Funcional'],
    diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
    fechaSeleccionada,
    filtros = {},
}: ClasesProps) {
    const [vistaActiva, setVistaActiva] = useState<'socio' | 'admin'>('socio');
    const [claseParaInscribir, setClaseParaInscribir] = useState<ClaseItem | null>(null);
    const [claseParaVerInscritos, setClaseParaVerInscritos] = useState<ClaseItem | null>(null);
    const [modalCrearClase, setModalCrearClase] = useState(false);
    const [claseParaEditar, setClaseParaEditar] = useState<ClaseItem | null>(null);

    // Filtros locales
    const [filtroDisciplina, setFiltroDisciplina] = useState(filtros.disciplina || '');
    const [filtroSucursal, setFiltroSucursal] = useState<string>(filtros.sucursal_id ? String(filtros.sucursal_id) : '');
    const [filtroDia, setFiltroDia] = useState(filtros.dia_semana || '');
    const [fechaFiltro, setFechaFiltro] = useState(fechaSeleccionada || new Date().toISOString().split('T')[0]);

    // Formulario de inscripción
    const [socioSeleccionadoId, setSocioSeleccionadoId] = useState<string>('');
    const [enviandoInscripcion, setEnviandoInscripcion] = useState(false);
    const [mensajeErrorInscripcion, setMensajeErrorInscripcion] = useState<string | null>(null);

    // Formulario de creación/edición de clase
    const formClase = useForm({
        nombre: '',
        profesor_id: '',
        sucursal_id: '',
        dia_semana: 'Lunes',
        hora_inicio: '08:00',
        duracion_minutos: 60,
        cupo_maximo: 20,
        descripcion: '',
        sala: 'Sala Principal',
    });

    // Helper para buscar socio seleccionado
    const socioActivo = socios.find((s) => String(s.id) === socioSeleccionadoId);

    // Aplicar filtros en servidor
    const aplicarFiltros = (nuevaDisciplina?: string, nuevaSucursal?: string, nuevoDia?: string, nuevaFecha?: string) => {
        router.get(
            route('clases.index'),
            {
                disciplina: nuevaDisciplina !== undefined ? nuevaDisciplina : filtroDisciplina,
                sucursal_id: nuevaSucursal !== undefined ? nuevaSucursal : filtroSucursal,
                dia_semana: nuevoDia !== undefined ? nuevoDia : filtroDia,
                fecha: nuevaFecha !== undefined ? nuevaFecha : fechaFiltro,
            },
            { preserveState: true, replace: true }
        );
    };

    // Envío de inscripción
    const handleInscribir = (e: React.FormEvent) => {
        e.preventDefault();
        if (!claseParaInscribir || !socioSeleccionadoId) return;

        setEnviandoInscripcion(true);
        setMensajeErrorInscripcion(null);

        router.post(
            route('clases.inscribir', { clase: claseParaInscribir.id }),
            {
                socio_id: socioSeleccionadoId,
                clase_grupal_id: claseParaInscribir.id,
                fecha_clase: fechaFiltro,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setClaseParaInscribir(null);
                    setSocioSeleccionadoId('');
                    setEnviandoInscripcion(false);
                },
                onError: (errors) => {
                    setEnviandoInscripcion(false);
                    const primerError = Object.values(errors)[0];
                    setMensajeErrorInscripcion(typeof primerError === 'string' ? primerError : 'Error al procesar inscripción.');
                },
            }
        );
    };

    // Confirmar asistencia
    const handleConfirmarAsistencia = (inscripcionId: number) => {
        router.patch(
            route('clases.inscripciones.confirmar', { inscripcion: inscripcionId }),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Actualizar estado local si está abierto el modal de inscritos
                    if (claseParaVerInscritos) {
                        const inscripcionesActualizadas = claseParaVerInscritos.inscripciones.map((ins) =>
                            ins.id === inscripcionId ? { ...ins, asistencia_confirmada: true, estado: 'asistio' } : ins
                        );
                        setClaseParaVerInscritos({ ...claseParaVerInscritos, inscripciones: inscripcionesActualizadas });
                    }
                },
            }
        );
    };

    // Cancelar inscripción
    const handleCancelarInscripcion = (inscripcionId: number) => {
        if (!confirm('¿Estás seguro de cancelar esta inscripción a la clase?')) return;

        router.delete(route('clases.inscripciones.destroy', { inscripcion: inscripcionId }), {
            preserveScroll: true,
            onSuccess: () => {
                if (claseParaVerInscritos) {
                    const inscripcionesActualizadas = claseParaVerInscritos.inscripciones.filter((ins) => ins.id !== inscripcionId);
                    setClaseParaVerInscritos({ ...claseParaVerInscritos, inscripciones: inscripcionesActualizadas });
                }
            },
        });
    };

    // Guardar nueva clase o actualización
    const handleGuardarClase = (e: React.FormEvent) => {
        e.preventDefault();
        if (claseParaEditar) {
            formClase.put(route('clases.update', { clase: claseParaEditar.id }), {
                onSuccess: () => {
                    setModalCrearClase(false);
                    setClaseParaEditar(null);
                    formClase.reset();
                },
            });
        } else {
            formClase.post(route('clases.store'), {
                onSuccess: () => {
                    setModalCrearClase(false);
                    formClase.reset();
                },
            });
        }
    };

    // Abrir modal de edición
    const handleAbrirEditar = (clase: ClaseItem) => {
        setClaseParaEditar(clase);
        formClase.setData({
            nombre: clase.nombre,
            profesor_id: clase.profesor ? String(clase.profesor.id) : '',
            sucursal_id: clase.sucursal ? String(clase.sucursal.id) : '',
            dia_semana: clase.dia_semana || 'Lunes',
            hora_inicio: clase.hora_inicio || '08:00',
            duracion_minutos: clase.duracion_minutos || 60,
            cupo_maximo: clase.cupo_maximo || 20,
            descripcion: clase.descripcion || '',
            sala: clase.sala || 'Sala Principal',
        });
        setModalCrearClase(true);
    };

    // Eliminar clase
    const handleEliminarClase = (clase: ClaseItem) => {
        if (!confirm(`¿Deseas deshabilitar la clase ${clase.nombre}?`)) return;
        router.delete(route('clases.destroy', { clase: clase.id }), { preserveScroll: true });
    };

    // Colores e íconos por disciplina deportiva
    const getDisciplinaStyle = (nombre: string) => {
        const n = nombre.toLowerCase();
        if (n.includes('spinning')) {
            return {
                border: 'border-cyan-500/30',
                bg: 'bg-cyan-500/10',
                text: 'text-cyan-400',
                badge: 'bg-cyan-950 text-cyan-300 border-cyan-700/50',
                icon: Activity,
            };
        }
        if (n.includes('yoga')) {
            return {
                border: 'border-purple-500/30',
                bg: 'bg-purple-500/10',
                text: 'text-purple-400',
                badge: 'bg-purple-950 text-purple-300 border-purple-700/50',
                icon: HeartPulse,
            };
        }
        if (n.includes('pilates')) {
            return {
                border: 'border-amber-500/30',
                bg: 'bg-amber-500/10',
                text: 'text-amber-400',
                badge: 'bg-amber-950 text-amber-300 border-amber-700/50',
                icon: Sparkles,
            };
        }
        if (n.includes('zumba')) {
            return {
                border: 'border-pink-500/30',
                bg: 'bg-pink-500/10',
                text: 'text-pink-400',
                badge: 'bg-pink-950 text-pink-300 border-pink-700/50',
                icon: Flame,
            };
        }
        // Funcional o por defecto
        return {
            border: 'border-lime-500/30',
            bg: 'bg-lime-500/10',
            text: 'text-lime-400',
            badge: 'bg-lime-950 text-lime-300 border-lime-700/50',
            icon: Dumbbell,
        };
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Clases Grupales - FitLife" />

            <div className="space-y-6 p-4 md:p-8">
                {/* Encabezado Principal */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-lime-500/10 text-lime-400 ring-1 ring-lime-500/30">
                                <CalendarDays className="h-5 w-5" />
                            </span>
                            <h1 className="text-2xl font-bold tracking-tight text-white md:text-3xl">
                                Clases Grupales <span className="text-lime-400">&amp; Aforo en Tiempo Real</span>
                            </h1>
                        </div>
                        <p className="mt-1 text-sm text-slate-400">
                            Spinning, Yoga, Pilates, Zumba y Funcional. Gestión multi-sucursal con aforo dinámico y reglas por membresía.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        {/* Selector de Fecha */}
                        <div className="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-900/90 px-3 py-1.5 shadow-sm">
                            <Calendar className="h-4 w-4 text-slate-400" />
                            <input
                                type="date"
                                value={fechaFiltro}
                                onChange={(e) => {
                                    setFechaFiltro(e.target.value);
                                    aplicarFiltros(undefined, undefined, undefined, e.target.value);
                                }}
                                className="bg-transparent text-sm font-medium text-slate-200 outline-none"
                            />
                        </div>

                        {/* Switch de Vista (Socio vs Admin) */}
                        <div className="flex rounded-lg border border-slate-800 bg-slate-900 p-1">
                            <button
                                type="button"
                                onClick={() => setVistaActiva('socio')}
                                className={`flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition ${
                                    vistaActiva === 'socio'
                                        ? 'bg-lime-500 text-slate-950 shadow'
                                        : 'text-slate-400 hover:text-white'
                                }`}
                            >
                                <CalendarDays className="h-3.5 w-3.5" />
                                Cartelera / Socios
                            </button>
                            <button
                                type="button"
                                onClick={() => setVistaActiva('admin')}
                                className={`flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition ${
                                    vistaActiva === 'admin'
                                        ? 'bg-lime-500 text-slate-950 shadow'
                                        : 'text-slate-400 hover:text-white'
                                }`}
                            >
                                <Users className="h-3.5 w-3.5" />
                                Gestión &amp; Cupos
                            </button>
                        </div>

                        {/* Botón Nueva Clase (Admin) */}
                        <button
                            type="button"
                            onClick={() => {
                                setClaseParaEditar(null);
                                formClase.reset();
                                setModalCrearClase(true);
                            }}
                            className="inline-flex items-center gap-2 rounded-lg bg-lime-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow transition hover:bg-lime-400 active:scale-95"
                        >
                            <Plus className="h-4 w-4" />
                            Nueva Clase
                        </button>
                    </div>
                </div>

                {/* Barra de Filtros Rápidos */}
                <div className="rounded-xl border border-slate-800 bg-slate-900/70 p-4 backdrop-blur">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        {/* Filtro por Disciplinas */}
                        <div className="flex flex-wrap items-center gap-1.5">
                            <button
                                type="button"
                                onClick={() => {
                                    setFiltroDisciplina('');
                                    aplicarFiltros('');
                                }}
                                className={`rounded-lg px-3 py-1 text-xs font-medium transition ${
                                    filtroDisciplina === ''
                                        ? 'bg-white text-slate-950 font-bold'
                                        : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700'
                                }`}
                            >
                                Todas las Disciplinas
                            </button>
                            {disciplinas.map((disc) => {
                                const style = getDisciplinaStyle(disc);
                                const IconComponent = style.icon;
                                const activo = filtroDisciplina.toLowerCase() === disc.toLowerCase();
                                return (
                                    <button
                                        key={disc}
                                        type="button"
                                        onClick={() => {
                                            const val = activo ? '' : disc;
                                            setFiltroDisciplina(val);
                                            aplicarFiltros(val);
                                        }}
                                        className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-medium transition border ${
                                            activo
                                                ? `${style.badge} font-bold ring-1 ring-lime-400`
                                                : 'border-slate-800 bg-slate-950 text-slate-300 hover:border-slate-700'
                                        }`}
                                    >
                                        <IconComponent className="h-3 w-3" />
                                        {disc}
                                    </button>
                                );
                            })}
                        </div>

                        {/* Filtros Dropdowns (Sucursal y Día) */}
                        <div className="flex flex-wrap items-center gap-3">
                            <select
                                value={filtroSucursal}
                                onChange={(e) => {
                                    setFiltroSucursal(e.target.value);
                                    aplicarFiltros(undefined, e.target.value);
                                }}
                                className="rounded-lg border border-slate-800 bg-slate-950 px-3 py-1.5 text-xs text-slate-200 outline-none focus:border-lime-500"
                            >
                                <option value="">Todas las Sedes / Sucursales</option>
                                {sucursales.map((suc) => (
                                    <option key={suc.id} value={suc.id}>
                                        {suc.nombre} ({suc.ciudad})
                                    </option>
                                ))}
                            </select>

                            <select
                                value={filtroDia}
                                onChange={(e) => {
                                    setFiltroDia(e.target.value);
                                    aplicarFiltros(undefined, undefined, e.target.value);
                                }}
                                className="rounded-lg border border-slate-800 bg-slate-950 px-3 py-1.5 text-xs text-slate-200 outline-none focus:border-lime-500"
                            >
                                <option value="">Cualquier día</option>
                                {diasSemana.map((dia) => (
                                    <option key={dia} value={dia}>
                                        {dia}
                                    </option>
                                ))}
                            </select>

                            {(filtroDisciplina || filtroSucursal || filtroDia) && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setFiltroDisciplina('');
                                        setFiltroSucursal('');
                                        setFiltroDia('');
                                        aplicarFiltros('', '', '');
                                    }}
                                    className="text-xs text-slate-400 hover:text-white underline"
                                >
                                    Limpiar filtros
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                {/* VISTA 1: CARTELERA DE SOCIOS (Tarjetas y Disponibilidad) */}
                {vistaActiva === 'socio' && (
                    <div className="space-y-4">
                        <div className="flex items-center justify-between text-xs text-slate-400">
                            <span>
                                Mostrando <strong className="text-white">{clases.length}</strong> clases activas para la fecha:{' '}
                                <strong className="text-lime-400">{fechaFiltro}</strong>
                            </span>
                            <span className="flex items-center gap-3">
                                <span className="inline-flex items-center gap-1 text-lime-400">
                                    <span className="h-2 w-2 rounded-full bg-lime-400 animate-pulse" /> Cupos disponibles
                                </span>
                                <span className="inline-flex items-center gap-1 text-rose-400">
                                    <span className="h-2 w-2 rounded-full bg-rose-400" /> Clase Llena
                                </span>
                            </span>
                        </div>

                        {clases.length === 0 ? (
                            <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center">
                                <CalendarDays className="h-12 w-12 text-slate-600 mb-3" />
                                <h3 className="text-lg font-medium text-white">No se encontraron clases con los filtros seleccionados</h3>
                                <p className="mt-1 text-sm text-slate-400">
                                    Intenta cambiando la disciplina, el día o la sucursal seleccionada.
                                </p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                                {clases.map((clase) => {
                                    const style = getDisciplinaStyle(clase.nombre);
                                    const IconComponent = style.icon;
                                    const ocupacion = Math.round(
                                        ((clase.cupo_maximo - clase.cupos_restantes) / clase.cupo_maximo) * 100
                                    );

                                    return (
                                        <div
                                            key={clase.id}
                                            className="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-lg backdrop-blur transition hover:border-slate-700 hover:shadow-2xl"
                                        >
                                            {/* Cabecera de la tarjeta */}
                                            <div>
                                                <div className="flex items-start justify-between gap-3">
                                                    <div className="flex items-center gap-3">
                                                        <div
                                                            className={`flex h-11 w-11 items-center justify-center rounded-xl ${style.bg} ${style.text} ring-1 ${style.border}`}
                                                        >
                                                            <IconComponent className="h-6 w-6" />
                                                        </div>
                                                        <div>
                                                            <span
                                                                className={`inline-block rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ${style.badge}`}
                                                            >
                                                                {clase.nombre}
                                                            </span>
                                                            <h3 className="text-base font-bold text-white group-hover:text-lime-400 transition">
                                                                {clase.nombre}
                                                            </h3>
                                                        </div>
                                                    </div>

                                                    {/* Badge de aforo */}
                                                    {clase.esta_llena ? (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-rose-950/80 px-2.5 py-1 text-xs font-semibold text-rose-300 ring-1 ring-rose-500/40">
                                                            <XCircle className="h-3.5 w-3.5" />
                                                            Clase Llena
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-lime-950/80 px-2.5 py-1 text-xs font-semibold text-lime-300 ring-1 ring-lime-500/40">
                                                            <CheckCircle2 className="h-3.5 w-3.5" />
                                                            {clase.cupos_restantes} cupos libres
                                                        </span>
                                                    )}
                                                </div>

                                                {/* Descripción */}
                                                {clase.descripcion && (
                                                    <p className="mt-3 text-xs leading-relaxed text-slate-400 line-clamp-2">
                                                        {clase.descripcion}
                                                    </p>
                                                )}

                                                {/* Barra de Ocupación */}
                                                <div className="mt-4 space-y-1">
                                                    <div className="flex justify-between text-[11px] text-slate-400">
                                                        <span>Aforo ocupado</span>
                                                        <span className="font-semibold text-slate-200">
                                                            {clase.cupo_maximo - clase.cupos_restantes} / {clase.cupo_maximo} alumnos ({ocupacion}%)
                                                        </span>
                                                    </div>
                                                    <div className="h-1.5 w-full overflow-hidden rounded-full bg-slate-800">
                                                        <div
                                                            className={`h-full transition-all duration-500 ${
                                                                clase.esta_llena ? 'bg-rose-500' : ocupacion > 75 ? 'bg-amber-500' : 'bg-lime-500'
                                                            }`}
                                                            style={{ width: `${ocupacion}%` }}
                                                        />
                                                    </div>
                                                </div>

                                                {/* Datos Técnicos (Profesor, Horario, Sede, Sala) */}
                                                <div className="mt-4 space-y-2 border-t border-slate-800/80 pt-3 text-xs text-slate-300">
                                                    <div className="flex items-center gap-2">
                                                        <User className="h-3.5 w-3.5 text-lime-400" />
                                                        <span className="text-slate-400">Profesor:</span>
                                                        <span className="font-medium text-white">
                                                            {clase.profesor?.nombre_completo || 'Por Asignar'}
                                                        </span>
                                                        {clase.profesor?.especialidad && (
                                                            <span className="text-[11px] text-slate-500">
                                                                ({clase.profesor.especialidad})
                                                            </span>
                                                        )}
                                                    </div>

                                                    <div className="flex items-center gap-2">
                                                        <Clock className="h-3.5 w-3.5 text-cyan-400" />
                                                        <span className="text-slate-400">Horario:</span>
                                                        <span className="font-medium text-slate-200">
                                                            {clase.dia_semana} a las {clase.hora_inicio} ({clase.duracion_minutos} min)
                                                        </span>
                                                    </div>

                                                    <div className="flex items-center gap-2">
                                                        <MapPin className="h-3.5 w-3.5 text-purple-400" />
                                                        <span className="text-slate-400">Sede:</span>
                                                        <span className="font-medium text-slate-200">
                                                            {clase.sucursal?.nombre || 'Sede Principal'}
                                                        </span>
                                                        {clase.sala && (
                                                            <span className="text-[11px] text-slate-500">• {clase.sala}</span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Botones de Acción */}
                                            <div className="mt-5 flex items-center gap-2 pt-2">
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setClaseParaInscribir(clase);
                                                        setMensajeErrorInscripcion(null);
                                                    }}
                                                    disabled={clase.esta_llena}
                                                    className={`flex-1 rounded-xl px-4 py-2.5 text-xs font-bold transition flex items-center justify-center gap-1.5 ${
                                                        clase.esta_llena
                                                            ? 'bg-slate-800 text-slate-500 cursor-not-allowed'
                                                            : 'bg-lime-500 text-slate-950 shadow hover:bg-lime-400 active:scale-95'
                                                    }`}
                                                >
                                                    <Plus className="h-3.5 w-3.5" />
                                                    {clase.esta_llena ? 'Sin Cupos' : 'Inscribir Socio'}
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => setClaseParaVerInscritos(clase)}
                                                    className="rounded-xl border border-slate-700 bg-slate-800/80 px-3 py-2.5 text-xs font-semibold text-slate-200 transition hover:bg-slate-700 hover:text-white"
                                                    title="Ver lista de inscritos"
                                                >
                                                    <Users className="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                )}

                {/* VISTA 2: GESTIÓN DE CLASES & CUPOS (Administración Tabla / Grid) */}
                {vistaActiva === 'admin' && (
                    <div className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                        <div className="border-b border-slate-800 px-6 py-4 flex items-center justify-between">
                            <div>
                                <h2 className="text-base font-bold text-white">Inventario y Configuración de Clases</h2>
                                <p className="text-xs text-slate-400">
                                    Control de cupos, asignación de profesores por sede y lista de asistentes.
                                </p>
                            </div>
                            <span className="text-xs text-slate-400 font-mono">
                                Total: {clases.length} clases registradas
                            </span>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs text-slate-300">
                                <thead className="border-b border-slate-800 bg-slate-950/70 uppercase tracking-wider text-[10px] text-slate-400 font-semibold">
                                    <tr>
                                        <th className="px-5 py-3">Disciplina / Clase</th>
                                        <th className="px-5 py-3">Profesor Asignado</th>
                                        <th className="px-5 py-3">Sucursal / Sala</th>
                                        <th className="px-5 py-3">Día &amp; Horario</th>
                                        <th className="px-5 py-3 text-center">Aforo &amp; Cupos</th>
                                        <th className="px-5 py-3 text-center">Inscritos Hoy</th>
                                        <th className="px-5 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800/70">
                                    {clases.map((clase) => {
                                        const style = getDisciplinaStyle(clase.nombre);
                                        const IconComponent = style.icon;

                                        return (
                                            <tr key={clase.id} className="hover:bg-slate-800/40 transition">
                                                <td className="px-5 py-3.5">
                                                    <div className="flex items-center gap-2.5">
                                                        <div className={`p-1.5 rounded-lg ${style.bg} ${style.text}`}>
                                                            <IconComponent className="h-4 w-4" />
                                                        </div>
                                                        <div>
                                                            <div className="font-bold text-white">{clase.nombre}</div>
                                                            <div className="text-[11px] text-slate-400">
                                                                {clase.duracion_minutos} minutos
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="px-5 py-3.5">
                                                    <div className="font-medium text-slate-200">
                                                        {clase.profesor?.nombre_completo || 'Sin asignar'}
                                                    </div>
                                                    <div className="text-[11px] text-slate-500">
                                                        {clase.profesor?.especialidad || 'Profesor de planta'}
                                                    </div>
                                                </td>

                                                <td className="px-5 py-3.5">
                                                    <div className="font-medium text-slate-200">
                                                        {clase.sucursal?.nombre || 'Sede Central'}
                                                    </div>
                                                    <div className="text-[11px] text-slate-500">
                                                        {clase.sala || 'Sala general'}
                                                    </div>
                                                </td>

                                                <td className="px-5 py-3.5">
                                                    <span className="inline-block rounded bg-slate-800 px-2 py-0.5 font-medium text-slate-200">
                                                        {clase.dia_semana}
                                                    </span>
                                                    <span className="ml-1 text-slate-400 font-mono">
                                                        {clase.hora_inicio}
                                                    </span>
                                                </td>

                                                <td className="px-5 py-3.5 text-center">
                                                    <div className="font-bold">
                                                        {clase.esta_llena ? (
                                                            <span className="text-rose-400">0 cupos</span>
                                                        ) : (
                                                            <span className="text-lime-400">
                                                                {clase.cupos_restantes} disponibles
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div className="text-[10px] text-slate-500">
                                                        Máx: {clase.cupo_maximo}
                                                    </div>
                                                </td>

                                                <td className="px-5 py-3.5 text-center">
                                                    <button
                                                        type="button"
                                                        onClick={() => setClaseParaVerInscritos(clase)}
                                                        className="inline-flex items-center gap-1 rounded-full bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-700"
                                                    >
                                                        <Users className="h-3 w-3 text-cyan-400" />
                                                        {clase.inscripciones.length} socios
                                                    </button>
                                                </td>

                                                <td className="px-5 py-3.5 text-right">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setClaseParaInscribir(clase);
                                                                setMensajeErrorInscripcion(null);
                                                            }}
                                                            disabled={clase.esta_llena}
                                                            title="Inscribir socio"
                                                            className="rounded-lg p-1.5 text-lime-400 hover:bg-lime-500/10 disabled:opacity-30"
                                                        >
                                                            <Plus className="h-4 w-4" />
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleAbrirEditar(clase)}
                                                            title="Editar clase"
                                                            className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white"
                                                        >
                                                            <Edit3 className="h-4 w-4" />
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleEliminarClase(clase)}
                                                            title="Eliminar clase"
                                                            className="rounded-lg p-1.5 text-rose-400 hover:bg-rose-500/10"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* MODAL 1: INSCRIPCIÓN INTELIGENTE CON REGLAS DE NEGOCIO POR PLAN */}
                {claseParaInscribir && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
                        <div className="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                            {/* Cerrar modal */}
                            <button
                                type="button"
                                onClick={() => {
                                    setClaseParaInscribir(null);
                                    setSocioSeleccionadoId('');
                                }}
                                className="absolute right-4 top-4 rounded-lg p-1 text-slate-400 hover:bg-slate-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>

                            {/* Cabecera del modal */}
                            <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-500/10 text-lime-400 ring-1 ring-lime-500/30">
                                    <CalendarDays className="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 className="text-lg font-bold text-white">Inscripción a Clase Grupal</h3>
                                    <p className="text-xs text-slate-400">
                                        {claseParaInscribir.nombre} • {claseParaInscribir.sucursal?.nombre || 'Sede Principal'}
                                    </p>
                                </div>
                            </div>

                            <form onSubmit={handleInscribir} className="mt-4 space-y-4">
                                {/* Información de la clase seleccionada */}
                                <div className="rounded-xl border border-slate-800 bg-slate-950 p-3 text-xs space-y-1.5">
                                    <div className="flex justify-between">
                                        <span className="text-slate-400">Día y Hora:</span>
                                        <span className="font-semibold text-slate-200">
                                            {claseParaInscribir.dia_semana} {claseParaInscribir.hora_inicio} ({claseParaInscribir.duracion_minutos} min)
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-slate-400">Profesor:</span>
                                        <span className="font-semibold text-slate-200">
                                            {claseParaInscribir.profesor?.nombre_completo || 'Profesor asignado'}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-slate-400">Cupos restantes para la fecha:</span>
                                        <span className="font-bold text-lime-400">
                                            {claseParaInscribir.cupos_restantes} cupos disponibles
                                        </span>
                                    </div>
                                </div>

                                {/* Selección de Fecha de la clase */}
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                                        Fecha de la sesión:
                                    </label>
                                    <input
                                        type="date"
                                        value={fechaFiltro}
                                        onChange={(e) => setFechaFiltro(e.target.value)}
                                        required
                                        className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                    />
                                </div>

                                {/* Selector de Socio */}
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                                        Seleccionar Socio Activo:
                                    </label>
                                    <select
                                        value={socioSeleccionadoId}
                                        onChange={(e) => {
                                            setSocioSeleccionadoId(e.target.value);
                                            setMensajeErrorInscripcion(null);
                                        }}
                                        required
                                        className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2.5 text-xs text-slate-200 outline-none focus:border-lime-500"
                                    >
                                        <option value="">-- Buscar socio por nombre o código --</option>
                                        {socios.map((socio) => (
                                            <option key={socio.id} value={socio.id}>
                                                {socio.nombre_completo} ({socio.codigo}) — {socio.plan_nombre}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* EVALUACIÓN DE REGLAS DE NEGOCIO EN TIEMPO REAL */}
                                {socioActivo && (
                                    <div className="space-y-3">
                                        {/* Caso 1: Plan Básico ($50/mes) -> Denegado */}
                                        {socioActivo.es_basico && (
                                            <div className="rounded-xl border border-rose-500/40 bg-rose-950/50 p-4 text-xs text-rose-200">
                                                <div className="flex items-start gap-2.5">
                                                    <AlertCircle className="h-5 w-5 text-rose-400 shrink-0 mt-0.5" />
                                                    <div>
                                                        <div className="font-bold text-rose-300">
                                                            Acceso Denegado: Plan Básico ($50/mes)
                                                        </div>
                                                        <p className="mt-1 leading-relaxed text-rose-200/90">
                                                            El Plan Básico no incluye acceso a clases grupales.
                                                            Actualiza a <strong>Plan Completo</strong> ($80) o{' '}
                                                            <strong>Plan Premium</strong> ($120) para inscribirte.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        )}

                                        {/* Caso 2: Plan Completo ($80/mes) -> Recargo de $10 */}
                                        {socioActivo.es_completo && (
                                            <div className="rounded-xl border border-cyan-500/40 bg-cyan-950/50 p-4 text-xs text-cyan-200">
                                                <div className="flex items-start gap-2.5">
                                                    <DollarSign className="h-5 w-5 text-cyan-400 shrink-0 mt-0.5" />
                                                    <div>
                                                        <div className="font-bold text-cyan-300">
                                                            Plan Completo ($80/mes) — Recargo Obligatorio
                                                        </div>
                                                        <p className="mt-1 leading-relaxed text-cyan-200/90">
                                                            Aplica un <strong>recargo obligatorio de $10.00</strong> por clase.
                                                            Se registrará el pago correspondiente en el historial del socio.
                                                        </p>
                                                        <div className="mt-2 inline-flex items-center gap-1 rounded bg-cyan-900/80 px-2 py-0.5 font-mono text-[11px] text-cyan-300">
                                                            Monto a cobrar: <strong>$10.00 USD</strong> (Estado: Pagado)
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        )}

                                        {/* Caso 3: Plan Premium ($120/mes) -> 100% Incluido */}
                                        {socioActivo.es_premium && (
                                            <div className="rounded-xl border border-lime-500/40 bg-lime-950/50 p-4 text-xs text-lime-200">
                                                <div className="flex items-start gap-2.5">
                                                    <Sparkles className="h-5 w-5 text-lime-400 shrink-0 mt-0.5" />
                                                    <div>
                                                        <div className="font-bold text-lime-300">
                                                            Plan Premium ($120/mes) — Acceso Total Incluido
                                                        </div>
                                                        <p className="mt-1 leading-relaxed text-lime-200/90">
                                                            ¡Clase 100% incluida sin costo adicional! No aplica recargo
                                                            ($0.00).
                                                        </p>
                                                        <div className="mt-2 inline-flex items-center gap-1 rounded bg-lime-900/80 px-2 py-0.5 font-mono text-[11px] text-lime-300">
                                                            Recargo: <strong>$0.00 USD</strong> (Estado: Exento)
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Mensaje de Error si la solicitud falló */}
                                {mensajeErrorInscripcion && (
                                    <div className="rounded-xl border border-rose-500/30 bg-rose-950/60 p-3 text-xs text-rose-300 flex items-center gap-2">
                                        <AlertCircle className="h-4 w-4 shrink-0" />
                                        <span>{mensajeErrorInscripcion}</span>
                                    </div>
                                )}

                                {/* Botones del Formulario */}
                                <div className="mt-5 flex items-center justify-end gap-2 border-t border-slate-800 pt-4">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setClaseParaInscribir(null);
                                            setSocioSeleccionadoId('');
                                        }}
                                        className="rounded-xl px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={enviandoInscripcion || (socioActivo?.es_basico ?? false)}
                                        className={`rounded-xl px-5 py-2 text-xs font-bold transition flex items-center gap-1.5 ${
                                            socioActivo?.es_basico
                                                ? 'bg-slate-800 text-slate-500 cursor-not-allowed'
                                                : 'bg-lime-500 text-slate-950 shadow hover:bg-lime-400 active:scale-95'
                                        }`}
                                    >
                                        {enviandoInscripcion ? 'Procesando...' : 'Confirmar Inscripción'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                {/* MODAL 2: LISTADO DE SOCIOS INSCRITOS Y CONFIRMACIÓN DE ASISTENCIA */}
                {claseParaVerInscritos && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
                        <div className="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                            <button
                                type="button"
                                onClick={() => setClaseParaVerInscritos(null)}
                                className="absolute right-4 top-4 rounded-lg p-1 text-slate-400 hover:bg-slate-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>

                            <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-400 ring-1 ring-cyan-500/30">
                                    <Users className="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 className="text-lg font-bold text-white">
                                        Socios Inscritos — {claseParaVerInscritos.nombre}
                                    </h3>
                                    <p className="text-xs text-slate-400">
                                        Fecha: <strong className="text-lime-400">{fechaFiltro}</strong> •{' '}
                                        {claseParaVerInscritos.inscripciones.length} alumnos registrados (Aforo:{' '}
                                        {claseParaVerInscritos.cupo_maximo})
                                    </p>
                                </div>
                            </div>

                            <div className="mt-4 max-h-96 overflow-y-auto pr-1">
                                {claseParaVerInscritos.inscripciones.length === 0 ? (
                                    <div className="py-12 text-center text-xs text-slate-400">
                                        No hay socios inscritos para esta clase en la fecha seleccionada.
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        {claseParaVerInscritos.inscripciones.map((ins) => (
                                            <div
                                                key={ins.id}
                                                className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-950 p-3 text-xs"
                                            >
                                                <div>
                                                    <div className="font-bold text-white">
                                                        {ins.socio_nombre || 'Socio'}
                                                    </div>
                                                    <div className="text-[11px] text-slate-400">
                                                        DNI: {ins.socio_dni || 'N/A'} • Código: {ins.socio_codigo || 'N/A'}
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-2 flex-wrap">
                                                    {/* Badge Recargo / Estado de Pago */}
                                                    <span
                                                        className={`rounded px-2 py-0.5 font-mono text-[10px] font-semibold ${
                                                            ins.monto_recargo > 0
                                                                ? 'bg-amber-950 text-amber-300 border border-amber-800/50'
                                                                : 'bg-emerald-950 text-emerald-300 border border-emerald-800/50'
                                                        }`}
                                                    >
                                                        {ins.monto_recargo > 0
                                                            ? `Recargo: $${ins.monto_recargo.toFixed(2)} (${ins.estado_pago})`
                                                            : `Incluido ($0.00)`}
                                                    </span>

                                                    {/* Asistencia */}
                                                    {ins.asistencia_confirmada ? (
                                                        <span className="inline-flex items-center gap-1 rounded bg-lime-950 px-2 py-0.5 text-[10px] font-bold text-lime-400 border border-lime-800/50">
                                                            <Check className="h-3 w-3" /> Asistió
                                                        </span>
                                                    ) : (
                                                        <button
                                                            type="button"
                                                            onClick={() => handleConfirmarAsistencia(ins.id)}
                                                            className="inline-flex items-center gap-1 rounded bg-slate-800 px-2 py-1 text-[11px] font-semibold text-slate-300 hover:bg-lime-500 hover:text-slate-950 transition"
                                                            title="Confirmar presencia"
                                                        >
                                                            <UserCheck className="h-3.5 w-3.5 text-lime-400" />
                                                            Confirmar Asistencia
                                                        </button>
                                                    )}

                                                    {/* Cancelar */}
                                                    <button
                                                        type="button"
                                                        onClick={() => handleCancelarInscripcion(ins.id)}
                                                        className="rounded p-1 text-slate-500 hover:text-rose-400"
                                                        title="Dar de baja de la clase"
                                                    >
                                                        <Trash2 className="h-3.5 w-3.5" />
                                                    </button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>

                            <div className="mt-5 flex justify-end border-t border-slate-800 pt-4">
                                <button
                                    type="button"
                                    onClick={() => setClaseParaVerInscritos(null)}
                                    className="rounded-xl bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700"
                                >
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* MODAL 3: CREAR / EDITAR CLASE GRUPAL (Admin) */}
                {modalCrearClase && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
                        <div className="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                            <button
                                type="button"
                                onClick={() => setModalCrearClase(false)}
                                className="absolute right-4 top-4 rounded-lg p-1 text-slate-400 hover:bg-slate-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>

                            <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-500/10 text-lime-400 ring-1 ring-lime-500/30">
                                    <Dumbbell className="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 className="text-lg font-bold text-white">
                                        {claseParaEditar ? 'Editar Clase Grupal' : 'Nueva Clase Grupal'}
                                    </h3>
                                    <p className="text-xs text-slate-400">
                                        Configura profesor, sede, horario y cupo máximo de alumnos.
                                    </p>
                                </div>
                            </div>

                            <form onSubmit={handleGuardarClase} className="mt-4 space-y-3">
                                {/* Disciplina */}
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                                        Nombre / Disciplina:
                                    </label>
                                    <select
                                        value={formClase.data.nombre}
                                        onChange={(e) => formClase.setData('nombre', e.target.value)}
                                        required
                                        className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                    >
                                        <option value="">-- Seleccione disciplina --</option>
                                        {disciplinas.map((d) => (
                                            <option key={d} value={d}>
                                                {d}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Profesor Asignado */}
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                                        Profesor Asignado:
                                    </label>
                                    <select
                                        value={formClase.data.profesor_id}
                                        onChange={(e) => formClase.setData('profesor_id', e.target.value)}
                                        required
                                        className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                    >
                                        <option value="">-- Seleccione profesor --</option>
                                        {profesores.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nombre_completo} ({p.especialidad})
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Sucursal */}
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                                        Sucursal / Sede:
                                    </label>
                                    <select
                                        value={formClase.data.sucursal_id}
                                        onChange={(e) => formClase.setData('sucursal_id', e.target.value)}
                                        required
                                        className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                    >
                                        <option value="">-- Seleccione sucursal --</option>
                                        {sucursales.map((s) => (
                                            <option key={s.id} value={s.id}>
                                                {s.nombre} ({s.ciudad})
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Día y Hora */}
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-300 mb-1">
                                            Día de la semana:
                                        </label>
                                        <select
                                            value={formClase.data.dia_semana}
                                            onChange={(e) => formClase.setData('dia_semana', e.target.value)}
                                            required
                                            className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                        >
                                            {diasSemana.map((dia) => (
                                                <option key={dia} value={dia}>
                                                    {dia}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-300 mb-1">
                                            Hora de inicio:
                                        </label>
                                        <input
                                            type="time"
                                            value={formClase.data.hora_inicio}
                                            onChange={(e) => formClase.setData('hora_inicio', e.target.value)}
                                            required
                                            className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                        />
                                    </div>
                                </div>

                                {/* Duración y Cupo */}
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-300 mb-1">
                                            Duración (minutos):
                                        </label>
                                        <input
                                            type="number"
                                            value={formClase.data.duracion_minutos}
                                            onChange={(e) => formClase.setData('duracion_minutos', parseInt(e.target.value) || 60)}
                                            min="15"
                                            max="180"
                                            required
                                            className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-300 mb-1">
                                            Cupo Máximo (Alumnos):
                                        </label>
                                        <input
                                            type="number"
                                            value={formClase.data.cupo_maximo}
                                            onChange={(e) => formClase.setData('cupo_maximo', parseInt(e.target.value) || 20)}
                                            min="1"
                                            max="100"
                                            required
                                            className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                        />
                                    </div>
                                </div>

                                {/* Sala */}
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                                        Sala / Espacio:
                                    </label>
                                    <input
                                        type="text"
                                        value={formClase.data.sala}
                                        onChange={(e) => formClase.setData('sala', e.target.value)}
                                        placeholder="Ej: Sala de Spinning, Studio 2, Box Funcional"
                                        className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500"
                                    />
                                </div>

                                {/* Descripción */}
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                                        Descripción breve:
                                    </label>
                                    <textarea
                                        value={formClase.data.descripcion}
                                        onChange={(e) => formClase.setData('descripcion', e.target.value)}
                                        rows={2}
                                        placeholder="Descripción de la disciplina, intensidad, etc."
                                        className="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 outline-none focus:border-lime-500 resize-none"
                                    />
                                </div>

                                {/* Botones */}
                                <div className="mt-5 flex items-center justify-end gap-2 border-t border-slate-800 pt-4">
                                    <button
                                        type="button"
                                        onClick={() => setModalCrearClase(false)}
                                        className="rounded-xl px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={formClase.processing}
                                        className="rounded-xl bg-lime-500 px-5 py-2 text-xs font-bold text-slate-950 shadow transition hover:bg-lime-400 active:scale-95 disabled:opacity-50"
                                    >
                                        {formClase.processing
                                            ? 'Guardando...'
                                            : claseParaEditar
                                            ? 'Actualizar Clase'
                                            : 'Crear Clase'}
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


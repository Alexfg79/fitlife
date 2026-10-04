import PaginadorCompacto, { type PaginatorMeta } from '@/components/paginador-compacto';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    Calendar,
    CheckCircle2,
    Clock,
    Edit3,
    Eye,
    Filter,
    LoaderCircle,
    LogOut,
    QrCode,
    Search,
    ShieldAlert,
    Trash2,
    User,
    UserCheck,
    X,
    XCircle,
    Zap,
} from 'lucide-react';
import React, { useState } from 'react';

interface AsistenciaRegistro {
    id: number;
    fecha_hora: string;
    fecha_hora_salida?: string | null;
    tiempo_permanencia_minutos?: number | null;
    metodo: string;
    estado_acceso: string;
    mensaje_acceso?: string;
    socio?: {
        id: number;
        nombre: string;
        apellido: string;
        dni: string;
        codigo: string;
    };
    registrado_por?: {
        name: string;
    };
}

interface AsistenciasProps {
    asistencias: PaginatorMeta & {
        data: AsistenciaRegistro[];
    };
    socio_seleccionado?: {
        id: number;
        nombre: string;
        apellido: string;
        codigo: string;
        dni: string;
    } | null;
    filtros: {
        fecha?: string;
        buscar?: string;
        estado?: string;
        socio_id?: string | number;
    };
    total_hoy: number;
    denegadas_hoy: number;
    en_sala_ahora?: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Control de Asistencias', href: '/asistencias' },
];

export default function AsistenciasIndex({
    asistencias,
    socio_seleccionado = null,
    filtros,
    total_hoy,
    denegadas_hoy,
    en_sala_ahora = 0,
}: AsistenciasProps) {
    const [fecha, setFecha] = useState(filtros.fecha || '');
    const [buscar, setBuscar] = useState(filtros.buscar || '');
    const [estado, setEstado] = useState(filtros.estado || '');
    const [generandoAuto, setGenerandoAuto] = useState(false);

    // Modal de Edición de Asistencia (CRUD)
    const [asistenciaAEditar, setAsistenciaAEditar] = useState<AsistenciaRegistro | null>(null);
    const [editFechaHora, setEditFechaHora] = useState('');
    const [editFechaHoraSalida, setEditFechaHoraSalida] = useState('');
    const [editEstado, setEditEstado] = useState('permitido');
    const [editMensaje, setEditMensaje] = useState('');
    const [actualizando, setActualizando] = useState(false);

    const handleAsistenciaAutomatica = () => {
        setGenerandoAuto(true);
        router.post(
            route('asistencias.generar-automatica'),
            {},
            {
                preserveScroll: true,
                onFinish: () => setGenerandoAuto(false),
            }
        );
    };

    const handleFiltrar = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('asistencias.index'),
            {
                fecha,
                buscar,
                estado,
                socio_id: filtros.socio_id || undefined,
            },
            { preserveState: true }
        );
    };

    const handleLimpiarFiltroSocio = () => {
        router.get(route('asistencias.index'), { fecha, buscar, estado });
    };

    const handleFiltrarPorSocio = (socioId: number) => {
        router.get(route('asistencias.index'), { socio_id: socioId });
    };

    const handleMarcarSalida = (asistenciaId: number, codigoSocio?: string) => {
        if (!codigoSocio) return;
        router.post(
            route('asistencias.checkout'),
            { codigo: codigoSocio, asistencia_id: asistenciaId },
            { preserveScroll: true }
        );
    };

    const handleAbrirEditar = (item: AsistenciaRegistro) => {
        setAsistenciaAEditar(item);
        // Formatear a datetime-local string (YYYY-MM-DDTHH:mm)
        const formatDt = (dtStr?: string | null) => {
            if (!dtStr) return '';
            const d = new Date(dtStr);
            const pad = (n: number) => (n < 10 ? `0${n}` : n);
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        };
        setEditFechaHora(formatDt(item.fecha_hora));
        setEditFechaHoraSalida(formatDt(item.fecha_hora_salida));
        setEditEstado(item.estado_acceso);
        setEditMensaje(item.mensaje_acceso || '');
    };

    const handleGuardarEdicion = (e: React.FormEvent) => {
        e.preventDefault();
        if (!asistenciaAEditar) return;
        setActualizando(true);

        router.put(
            route('asistencias.update', asistenciaAEditar.id),
            {
                fecha_hora: editFechaHora || undefined,
                fecha_hora_salida: editFechaHoraSalida || null,
                estado_acceso: editEstado,
                mensaje_acceso: editMensaje,
            },
            {
                onSuccess: () => {
                    setActualizando(false);
                    setAsistenciaAEditar(null);
                },
                onError: () => {
                    setActualizando(false);
                },
            }
        );
    };

    const handleEliminarAsistencia = (id: number) => {
        if (confirm('¿Estás seguro de eliminar este registro de asistencia del historial?')) {
            router.delete(route('asistencias.destroy', id), { preserveScroll: true });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Control de Asistencias - FitLife" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Cabecera */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                            <QrCode className="h-6 w-6 text-emerald-600" />
                            Registro y Control de Asistencias
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Histórico de ingresos en portería, torniquetes y trazabilidad de permanencia.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            disabled={generandoAuto}
                            onClick={handleAsistenciaAutomatica}
                            className="inline-flex items-center gap-2 rounded-xl border border-lime-500/40 bg-lime-500/10 px-4 py-2 text-sm font-bold text-lime-600 dark:text-lime-400 shadow-sm hover:bg-lime-500/20 hover:border-lime-500 transition cursor-pointer disabled:opacity-50"
                            title="Simula instantáneamente un check-in de socio activo en portería"
                        >
                            {generandoAuto ? (
                                <LoaderCircle className="h-4 w-4 animate-spin text-lime-600 dark:text-lime-400" />
                            ) : (
                                <Zap className="h-4 w-4 text-lime-600 dark:text-lime-400" />
                            )}
                            <span>{generandoAuto ? 'Marcando...' : '⚡ Asistencia Automática'}</span>
                        </button>

                        <div className="rounded-xl border border-emerald-200 bg-emerald-50/80 px-3.5 py-1.5 dark:border-emerald-800 dark:bg-emerald-950/40">
                            <span className="text-xs text-emerald-700 dark:text-emerald-300 block">En Sala Ahora</span>
                            <span className="text-lg font-bold text-emerald-900 dark:text-emerald-100">{en_sala_ahora}</span>
                        </div>
                        <div className="rounded-xl border border-blue-200 bg-blue-50/80 px-3.5 py-1.5 dark:border-blue-800 dark:bg-blue-950/40">
                            <span className="text-xs text-blue-700 dark:text-blue-300 block">Permitidas Hoy</span>
                            <span className="text-lg font-bold text-blue-900 dark:text-blue-100">{total_hoy}</span>
                        </div>
                        <div className="rounded-xl border border-rose-200 bg-rose-50/80 px-3.5 py-1.5 dark:border-rose-800 dark:bg-rose-950/40">
                            <span className="text-xs text-rose-700 dark:text-rose-300 block">Denegadas Hoy</span>
                            <span className="text-lg font-bold text-rose-900 dark:text-rose-100">{denegadas_hoy}</span>
                        </div>
                    </div>
                </div>

                {/* Banner de Indagación de Socio Individual */}
                {socio_seleccionado && (
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-cyan-500/30 bg-cyan-500/10 p-4 dark:border-cyan-500/20 dark:bg-cyan-950/40">
                        <div className="flex items-center gap-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-400 font-bold">
                                <User className="h-5 w-5" />
                            </div>
                            <div>
                                <span className="text-xs uppercase font-bold text-cyan-600 dark:text-cyan-400">
                                    Indagando Historial del Socio
                                </span>
                                <h3 className="text-base font-bold text-neutral-900 dark:text-white">
                                    {socio_seleccionado.nombre} {socio_seleccionado.apellido}
                                    <span className="ml-2 font-mono text-xs text-neutral-500 dark:text-neutral-400 font-normal">
                                        [{socio_seleccionado.codigo} • DNI: {socio_seleccionado.dni}]
                                    </span>
                                </h3>
                            </div>
                        </div>

                        <div className="flex items-center gap-2 self-end sm:self-auto">
                            <Link
                                href={route('socios.show', socio_seleccionado.id)}
                                className="inline-flex items-center gap-1.5 rounded-xl bg-cyan-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-cyan-700 transition"
                            >
                                <Eye className="h-4 w-4" />
                                Ver Expediente Completo
                            </Link>
                            <button
                                type="button"
                                onClick={handleLimpiarFiltroSocio}
                                className="inline-flex items-center gap-1 rounded-xl border border-neutral-300 bg-white px-3 py-2 text-xs font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 transition cursor-pointer"
                            >
                                <X className="h-3.5 w-3.5" />
                                Quitar Filtro
                            </button>
                        </div>
                    </div>
                )}

                {/* Filtros Responsivos */}
                <div className="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                    <form onSubmit={handleFiltrar} className="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label className="block text-xs font-semibold text-neutral-500 mb-1">Fecha</label>
                            <input
                                type="date"
                                value={fecha}
                                onChange={(e) => setFecha(e.target.value)}
                                className="w-full min-h-[44px] rounded-xl border border-neutral-300 bg-neutral-50 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-neutral-500 mb-1">Buscar Socio</label>
                            <div className="relative">
                                <Search className="absolute left-3 top-3.5 h-4 w-4 text-neutral-400" />
                                <input
                                    type="text"
                                    value={buscar}
                                    onChange={(e) => setBuscar(e.target.value)}
                                    placeholder="Nombre, DNI o Código..."
                                    className="w-full min-h-[44px] rounded-xl border border-neutral-300 bg-neutral-50 py-2 pl-9 pr-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                />
                            </div>
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-neutral-500 mb-1">Estado de Acceso</label>
                            <select
                                value={estado}
                                onChange={(e) => setEstado(e.target.value)}
                                className="w-full min-h-[44px] rounded-xl border border-neutral-300 bg-neutral-50 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                            >
                                <option value="">Todos los Estados</option>
                                <option value="permitido">Permitido</option>
                                <option value="denegado_vencido">Denegado por Cuota Vencida</option>
                                <option value="denegado_inactivo">Denegado por Socio Inactivo</option>
                                <option value="denegado_cupo">Denegado por Sala Llena</option>
                            </select>
                        </div>

                        <div className="flex items-end gap-2">
                            <button
                                type="submit"
                                className="w-full min-h-[44px] rounded-xl bg-neutral-900 py-2 text-xs font-bold text-white hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900 flex items-center justify-center transition active:scale-98 cursor-pointer"
                            >
                                Aplicar Filtros
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setFecha('');
                                    setBuscar('');
                                    setEstado('');
                                    router.get(route('asistencias.index'));
                                }}
                                className="min-h-[44px] rounded-xl border border-neutral-200 px-4 py-2 text-xs font-medium text-neutral-600 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-400 flex items-center justify-center transition cursor-pointer"
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                {/* Contenedor: Tabla Desktop vs. Tarjetas Móvil */}
                <div className="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                    {/* Versión Escritorio (md en adelante) */}
                    <div className="hidden md:block overflow-x-auto">
                        <table className="w-full text-left text-sm text-neutral-600 dark:text-neutral-300">
                            <thead className="border-b border-neutral-100 bg-neutral-50/70 text-xs font-semibold uppercase text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/50 dark:text-neutral-400">
                                <tr>
                                    <th className="py-3 px-4">Ingreso</th>
                                    <th className="py-3 px-4">Salida / Tiempo</th>
                                    <th className="py-3 px-4">Código</th>
                                    <th className="py-3 px-4">Socio (Indagar)</th>
                                    <th className="py-3 px-4">DNI</th>
                                    <th className="py-3 px-4">Método</th>
                                    <th className="py-3 px-4">Estado</th>
                                    <th className="py-3 px-4">Detalle</th>
                                    <th className="py-3 px-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                {asistencias.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={9} className="py-8 text-center text-neutral-500">
                                            No hay registros de asistencias con los criterios seleccionados.
                                        </td>
                                    </tr>
                                ) : (
                                    asistencias.data.map((item) => {
                                        const enSala = item.estado_acceso === 'permitido' && !item.fecha_hora_salida;
                                        return (
                                            <tr key={item.id} className="hover:bg-neutral-50/60 dark:hover:bg-neutral-800/40">
                                                <td className="py-3 px-4 font-mono text-xs">
                                                    {new Date(item.fecha_hora).toLocaleString('es-ES', {
                                                        day: '2-digit',
                                                        month: '2-digit',
                                                        year: 'numeric',
                                                        hour: '2-digit',
                                                        minute: '2-digit',
                                                    })}
                                                </td>
                                                <td className="py-3 px-4 font-mono text-xs">
                                                    {item.fecha_hora_salida ? (
                                                        <div>
                                                            <span>
                                                                {new Date(item.fecha_hora_salida).toLocaleTimeString('es-ES', {
                                                                    hour: '2-digit',
                                                                    minute: '2-digit',
                                                                })}
                                                            </span>
                                                            {item.tiempo_permanencia_minutos !== null && (
                                                                <span className="ml-1.5 rounded bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 text-[10px] text-neutral-500 font-bold">
                                                                    {item.tiempo_permanencia_minutos} min
                                                                </span>
                                                            )}
                                                        </div>
                                                    ) : enSala ? (
                                                        <span className="inline-flex items-center gap-1 rounded bg-amber-500/10 border border-amber-500/30 px-2 py-0.5 text-[11px] font-bold text-amber-500 animate-pulse">
                                                            En Sala
                                                        </span>
                                                    ) : (
                                                        <span className="text-neutral-400">-</span>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4 font-mono text-xs font-semibold text-neutral-900 dark:text-white">
                                                    {item.socio?.codigo || 'N/A'}
                                                </td>
                                                <td className="py-3 px-4">
                                                    {item.socio ? (
                                                        <Link
                                                            href={route('socios.show', item.socio.id)}
                                                            className="font-medium text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1"
                                                            title="Ver Expediente Completo del Socio"
                                                        >
                                                            <span>{item.socio.nombre} {item.socio.apellido}</span>
                                                        </Link>
                                                    ) : (
                                                        <span className="text-neutral-400">Socio Eliminado</span>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4 font-mono text-xs">{item.socio?.dni || '-'}</td>
                                                <td className="py-3 px-4">
                                                    <span className="uppercase text-[11px] font-semibold text-neutral-500 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">
                                                        {item.metodo}
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <span
                                                        className={`inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold ${
                                                            item.estado_acceso === 'permitido'
                                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300'
                                                                : 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300'
                                                        }`}
                                                    >
                                                        {item.estado_acceso === 'permitido' ? (
                                                            <>
                                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                                                Permitido
                                                            </>
                                                        ) : (
                                                            <>
                                                                <XCircle className="h-3.5 w-3.5" />
                                                                Denegado
                                                            </>
                                                        )}
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4 text-xs text-neutral-500 max-w-xs truncate" title={item.mensaje_acceso}>
                                                    {item.mensaje_acceso || '-'}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        {enSala && item.socio && (
                                                            <button
                                                                type="button"
                                                                onClick={() => handleMarcarSalida(item.id, item.socio?.codigo)}
                                                                className="rounded-md bg-amber-50 p-1.5 text-amber-600 hover:bg-amber-100 dark:bg-amber-950/60 dark:text-amber-400 transition"
                                                                title="Marcar Salida / Check-Out"
                                                            >
                                                                <LogOut className="h-3.5 w-3.5" />
                                                            </button>
                                                        )}
                                                        {item.socio && (
                                                            <>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleFiltrarPorSocio(item.socio!.id)}
                                                                    className="rounded-md bg-neutral-100 p-1.5 text-neutral-600 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700 transition"
                                                                    title="Filtrar asistencias de este socio"
                                                                >
                                                                    <Filter className="h-3.5 w-3.5" />
                                                                </button>
                                                                <Link
                                                                    href={route('socios.show', item.socio.id)}
                                                                    className="rounded-md bg-cyan-50 p-1.5 text-cyan-600 hover:bg-cyan-100 dark:bg-cyan-950/60 dark:text-cyan-400 transition"
                                                                    title="Ver Expediente Socio"
                                                                >
                                                                    <Eye className="h-3.5 w-3.5" />
                                                                </Link>
                                                            </>
                                                        )}
                                                        <button
                                                            type="button"
                                                            onClick={() => handleAbrirEditar(item)}
                                                            className="rounded-md bg-blue-50 p-1.5 text-blue-600 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-400 transition"
                                                            title="Editar Registro"
                                                        >
                                                            <Edit3 className="h-3.5 w-3.5" />
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleEliminarAsistencia(item.id)}
                                                            className="rounded-md bg-rose-50 p-1.5 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-400 transition"
                                                            title="Eliminar del historial"
                                                        >
                                                            <Trash2 className="h-3.5 w-3.5" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Versión Móvil: Tarjetas de Asistencia (block md:hidden) */}
                    <div className="block md:hidden divide-y divide-neutral-100 dark:divide-neutral-800/80">
                        {asistencias.data.length === 0 ? (
                            <div className="p-6 text-center text-sm text-neutral-500">
                                No hay registros de asistencias con los criterios seleccionados.
                            </div>
                        ) : (
                            asistencias.data.map((item) => {
                                const permitido = item.estado_acceso === 'permitido';
                                const enSala = permitido && !item.fecha_hora_salida;
                                return (
                                    <div key={item.id} className="p-4 space-y-2.5">
                                        <div className="flex items-center justify-between">
                                            <span
                                                className={`inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-bold ${
                                                    permitido
                                                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                                                        : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                                                }`}
                                            >
                                                {permitido ? (
                                                    <>
                                                        <CheckCircle2 className="h-3.5 w-3.5" />
                                                        Acceso Permitido
                                                    </>
                                                ) : (
                                                    <>
                                                        <XCircle className="h-3.5 w-3.5" />
                                                        Acceso Denegado
                                                    </>
                                                )}
                                            </span>

                                            <span className="font-mono text-xs font-bold text-neutral-500 dark:text-neutral-400">
                                                {new Date(item.fecha_hora).toLocaleTimeString('es-ES', {
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                })}
                                            </span>
                                        </div>

                                        <div className="flex items-center justify-between">
                                            <div>
                                                {item.socio ? (
                                                    <Link
                                                        href={route('socios.show', item.socio.id)}
                                                        className="font-bold text-sm text-neutral-900 dark:text-white hover:text-emerald-500 transition underline-offset-2 hover:underline block"
                                                    >
                                                        {item.socio.nombre} {item.socio.apellido}
                                                    </Link>
                                                ) : (
                                                    <h4 className="font-bold text-sm text-neutral-500">Socio Eliminado</h4>
                                                )}
                                                <span className="font-mono text-xs text-neutral-400">
                                                    {item.socio?.codigo || 'N/A'} • DNI: {item.socio?.dni || '-'}
                                                </span>
                                            </div>

                                            <span className="uppercase text-[10px] font-bold tracking-wider px-2 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-400">
                                                {item.metodo}
                                            </span>
                                        </div>

                                        {item.fecha_hora_salida && (
                                            <div className="flex items-center justify-between text-xs text-neutral-500 font-mono bg-neutral-50 dark:bg-neutral-800/40 p-2 rounded-lg">
                                                <span>Salida: {new Date(item.fecha_hora_salida).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</span>
                                                {item.tiempo_permanencia_minutos !== null && (
                                                    <span className="font-bold text-emerald-500">
                                                        ⏱ {item.tiempo_permanencia_minutos} min
                                                    </span>
                                                )}
                                            </div>
                                        )}

                                        {item.mensaje_acceso && (
                                            <p className="text-xs text-neutral-500 dark:text-slate-400 bg-neutral-50 dark:bg-slate-950/60 p-2.5 rounded-lg border border-neutral-100 dark:border-slate-800/80">
                                                {item.mensaje_acceso}
                                            </p>
                                        )}

                                        {/* Botones de acción táctiles en móvil */}
                                        <div className="flex flex-wrap items-center justify-end gap-1.5 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                                            {enSala && item.socio && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleMarcarSalida(item.id, item.socio?.codigo)}
                                                    className="inline-flex items-center gap-1 rounded-xl bg-amber-500/10 border border-amber-500/30 px-3 py-1.5 text-xs font-bold text-amber-500"
                                                >
                                                    <LogOut className="h-3.5 w-3.5" />
                                                    Marcar Salida
                                                </button>
                                            )}
                                            {item.socio && (
                                                <>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleFiltrarPorSocio(item.socio!.id)}
                                                        className="inline-flex items-center gap-1 rounded-xl bg-neutral-100 dark:bg-neutral-800 px-2.5 py-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-300"
                                                        title="Filtrar por este socio"
                                                    >
                                                        <Filter className="h-3.5 w-3.5" />
                                                        Filtrar
                                                    </button>
                                                    <Link
                                                        href={route('socios.show', item.socio.id)}
                                                        className="inline-flex items-center gap-1 rounded-xl bg-cyan-500/10 border border-cyan-500/30 px-2.5 py-1.5 text-xs font-bold text-cyan-400"
                                                    >
                                                        <Eye className="h-3.5 w-3.5" />
                                                        Expediente
                                                    </Link>
                                                </>
                                            )}
                                            <button
                                                type="button"
                                                onClick={() => handleAbrirEditar(item)}
                                                className="inline-flex items-center gap-1 rounded-xl bg-blue-500/10 border border-blue-500/30 px-2.5 py-1.5 text-xs font-bold text-blue-400"
                                            >
                                                <Edit3 className="h-3.5 w-3.5" />
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => handleEliminarAsistencia(item.id)}
                                                className="inline-flex items-center gap-1 rounded-xl bg-rose-500/10 border border-rose-500/30 px-2.5 py-1.5 text-xs font-bold text-rose-400"
                                            >
                                                <Trash2 className="h-3.5 w-3.5" />
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                );
                            })
                        )}
                    </div>

                    {/* Barra de Navegación Paginada Compacta (1-50 de 5.042 < >) */}
                    <div className="border-t border-neutral-100 dark:border-neutral-800/80 px-4 py-3 bg-neutral-50/50 dark:bg-neutral-900/50 flex items-center justify-end">
                        <PaginadorCompacto paginacion={asistencias} />
                    </div>
                </div>

                {/* Modal para Editar Registro de Asistencia (CRUD) */}
                {asistenciaAEditar && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                        <div className="w-full max-w-md rounded-2xl border border-neutral-200 bg-white p-5 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                            <div className="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                                <div>
                                    <h3 className="text-base font-bold text-neutral-900 dark:text-white flex items-center gap-2">
                                        <Edit3 className="h-4 w-4 text-emerald-600" />
                                        Editar Registro de Asistencia
                                    </h3>
                                    {asistenciaAEditar.socio && (
                                        <p className="text-xs text-neutral-500">
                                            Socio: {asistenciaAEditar.socio.nombre} {asistenciaAEditar.socio.apellido} ({asistenciaAEditar.socio.codigo})
                                        </p>
                                    )}
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setAsistenciaAEditar(null)}
                                    className="p-1.5 text-neutral-400 hover:text-neutral-600 rounded-lg"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <form onSubmit={handleGuardarEdicion} className="space-y-4 pt-4">
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
                                        Fecha y Hora de Ingreso
                                    </label>
                                    <input
                                        type="datetime-local"
                                        value={editFechaHora}
                                        onChange={(e) => setEditFechaHora(e.target.value)}
                                        className="w-full rounded-xl border border-neutral-300 bg-neutral-50 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
                                        Fecha y Hora de Salida (Opcional)
                                    </label>
                                    <input
                                        type="datetime-local"
                                        value={editFechaHoraSalida}
                                        onChange={(e) => setEditFechaHoraSalida(e.target.value)}
                                        className="w-full rounded-xl border border-neutral-300 bg-neutral-50 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
                                        Estado de Acceso
                                    </label>
                                    <select
                                        value={editEstado}
                                        onChange={(e) => setEditEstado(e.target.value)}
                                        className="w-full rounded-xl border border-neutral-300 bg-neutral-50 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                    >
                                        <option value="permitido">Permitido (Ingreso Autorizado)</option>
                                        <option value="denegado_vencido">Denegado por Cuota Vencida</option>
                                        <option value="denegado_inactivo">Denegado por Socio Inactivo</option>
                                        <option value="denegado_cupo">Denegado por Sala Llena</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
                                        Detalle o Mensaje de Auditoría
                                    </label>
                                    <textarea
                                        rows={2}
                                        value={editMensaje}
                                        onChange={(e) => setEditMensaje(e.target.value)}
                                        placeholder="Ej. Salida registrada manualmente por recepción..."
                                        className="w-full rounded-xl border border-neutral-300 bg-neutral-50 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                    />
                                </div>

                                <div className="flex justify-end gap-2 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                                    <button
                                        type="button"
                                        onClick={() => setAsistenciaAEditar(null)}
                                        className="rounded-xl border border-neutral-200 px-4 py-2 text-xs font-semibold text-neutral-600 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-400"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={actualizando}
                                        className="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition disabled:opacity-50"
                                    >
                                        {actualizando ? 'Guardando...' : 'Guardar Cambios'}
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

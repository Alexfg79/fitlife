import PaginadorCompacto, { type PaginatorMeta } from '@/components/paginador-compacto';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    AlertCircle,
    CheckCircle2,
    Clock,
    CreditCard,
    Edit,
    Eye,
    LoaderCircle,
    Plus,
    Search,
    Trash2,
    UserCheck,
    Users,
    XCircle,
    Zap,
} from 'lucide-react';
import React, { useState } from 'react';

interface SocioItem {
    id: number;
    codigo: string;
    nombre: string;
    apellido: string;
    dni: string;
    correo: string;
    telefono?: string;
    fecha_nacimiento?: string;
    estado: string;
    membresia_activa?: {
        id: number;
        plan?: { nombre: string };
        fecha_fin: string;
    };
    ultima_membresia?: {
        id: number;
        plan?: { nombre: string };
        fecha_fin: string;
        estado: string;
    };
}

interface PlanItem {
    id: number;
    nombre: string;
    precio: number | string;
    duracion_dias: number;
}

interface SociosIndexProps {
    socios: PaginatorMeta & {
        data: SocioItem[];
        links?: any[];
    };
    planes: PlanItem[];
    filtros: {
        buscar?: string;
        estado?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Socios y Membresías', href: '/socios' },
];

export default function SociosIndex({ socios, planes = [], filtros }: SociosIndexProps) {
    const [busqueda, setBusqueda] = useState(filtros.buscar || '');
    const [estadoFiltro, setEstadoFiltro] = useState(filtros.estado || '');

    // Modal de registro rápido de socio
    const [modalCrearAbierto, setModalCrearAbierto] = useState(false);
    const [nuevoSocio, setNuevoSocio] = useState({
        nombre: '',
        apellido: '',
        dni: '',
        correo: '',
        telefono: '',
        fecha_nacimiento: '',
        plan_id: planes[0]?.id ? String(planes[0].id) : '',
        metodo_pago: 'efectivo',
    });
    const [guardandoSocio, setGuardandoSocio] = useState(false);

    // Modal de renovación rápida
    const [socioParaRenovar, setSocioParaRenovar] = useState<SocioItem | null>(null);
    const [planRenovacion, setPlanRenovacion] = useState<number>(planes[0]?.id || 1);
    const [metodoPagoRenovacion, setMetodoPagoRenovacion] = useState('efectivo');
    const [renovando, setRenovando] = useState(false);
    const [generandoAuto, setGenerandoAuto] = useState(false);

    const handleGenerarAutomatico = () => {
        setGenerandoAuto(true);
        router.post(
            route('socios.generar-automatico'),
            {},
            {
                preserveScroll: true,
                onFinish: () => setGenerandoAuto(false),
            }
        );
    };

    const handleBuscar = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('socios.index'),
            { buscar: busqueda, estado: estadoFiltro },
            { preserveState: true }
        );
    };

    const handleCrearSocio = (e: React.FormEvent) => {
        e.preventDefault();
        setGuardandoSocio(true);
        router.post(route('socios.store'), nuevoSocio, {
            onSuccess: () => {
                setGuardandoSocio(false);
                setModalCrearAbierto(false);
                setNuevoSocio({
                    nombre: '',
                    apellido: '',
                    dni: '',
                    correo: '',
                    telefono: '',
                    fecha_nacimiento: '',
                    plan_id: planes[0]?.id ? String(planes[0].id) : '',
                    metodo_pago: 'efectivo',
                });
            },
            onError: () => {
                setGuardandoSocio(false);
            },
        });
    };

    const handleRenovarCuota = (e: React.FormEvent) => {
        e.preventDefault();
        if (!socioParaRenovar) return;
        setRenovando(true);
        router.post(
            route('membresias.store'),
            {
                socio_id: socioParaRenovar.id,
                plan_id: planRenovacion,
                metodo_pago: metodoPagoRenovacion,
            },
            {
                onSuccess: () => {
                    setRenovando(false);
                    setSocioParaRenovar(null);
                },
                onError: () => {
                    setRenovando(false);
                },
            }
        );
    };

    const handleEliminarSocio = (id: number, nombre: string) => {
        if (confirm(`¿Estás seguro de dar de baja al socio ${nombre}?`)) {
            router.delete(route('socios.destroy', id));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Socios - FitLife Gym" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Cabecera */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                            <Users className="h-6 w-6 text-emerald-600" />
                            Gestión de Socios y Membresías
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Padrón general de socios, estado de cuotas y renovación de planes.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <button
                            type="button"
                            disabled={generandoAuto}
                            onClick={handleGenerarAutomatico}
                            className="inline-flex items-center gap-2 rounded-lg border border-lime-500/40 bg-lime-500/10 px-3.5 py-2 text-sm font-bold text-lime-600 dark:text-lime-400 shadow-sm hover:bg-lime-500/20 hover:border-lime-500 transition cursor-pointer disabled:opacity-50"
                            title="Genera instantáneamente un nuevo socio con Faker, membresía activa y pago registrado"
                        >
                            {generandoAuto ? (
                                <LoaderCircle className="h-4 w-4 animate-spin text-lime-600 dark:text-lime-400" />
                            ) : (
                                <Zap className="h-4 w-4 text-lime-600 dark:text-lime-400" />
                            )}
                            <span>{generandoAuto ? 'Generando Socio...' : '⚡ Socio Automático'}</span>
                        </button>

                        <button
                            onClick={() => setModalCrearAbierto(true)}
                            className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 transition cursor-pointer"
                        >
                            <Plus className="h-4 w-4" />
                            Registrar Nuevo Socio
                        </button>
                    </div>
                </div>

                {/* Filtros y Búsqueda */}
                <div className="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                    <form onSubmit={handleBuscar} className="grid gap-3 sm:grid-cols-4">
                        <div className="relative sm:col-span-2">
                            <Search className="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
                            <input
                                type="text"
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                placeholder="Buscar por Nombre, DNI, Código o Correo..."
                                className="w-full rounded-lg border border-neutral-300 bg-neutral-50 py-2 pl-9 pr-3 text-sm text-neutral-900 focus:border-emerald-500 focus:bg-white focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <select
                                value={estadoFiltro}
                                onChange={(e) => setEstadoFiltro(e.target.value)}
                                className="w-full rounded-lg border border-neutral-300 bg-neutral-50 py-2 px-3 text-sm text-neutral-900 focus:border-emerald-500 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                            >
                                <option value="">Todos los Estados</option>
                                <option value="activo">Activos</option>
                                <option value="moroso">Morosos / Cuota Vencida</option>
                                <option value="inactivo">Inactivos</option>
                            </select>
                        </div>

                        <div className="flex gap-2">
                            <button
                                type="submit"
                                className="w-full rounded-lg bg-neutral-900 py-2 text-xs font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200"
                            >
                                Filtrar
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setBusqueda('');
                                    setEstadoFiltro('');
                                    router.get(route('socios.index'));
                                }}
                                className="rounded-lg border border-neutral-200 px-3 py-2 text-xs font-medium text-neutral-600 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-400"
                            >
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>

                {/* Contenedor Responsivo: Tabla para Desktop vs. Tarjetas para Móvil */}
                <div className="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                    {/* Versión Escritorio (md en adelante) */}
                    <div className="hidden md:block overflow-x-auto">
                        <table className="w-full text-left text-sm text-neutral-600 dark:text-neutral-300">
                            <thead className="border-b border-neutral-100 bg-neutral-50/70 text-xs font-semibold uppercase text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/50 dark:text-neutral-400">
                                <tr>
                                    <th className="py-3 px-4">Código / Carnet</th>
                                    <th className="py-3 px-4">Nombre y Apellido</th>
                                    <th className="py-3 px-4">DNI / Nacimiento</th>
                                    <th className="py-3 px-4">Contacto</th>
                                    <th className="py-3 px-4">Estado Socio</th>
                                    <th className="py-3 px-4">Membresía / Vencimiento</th>
                                    <th className="py-3 px-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                {socios.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="py-8 text-center text-neutral-500">
                                            No se encontraron socios registrados con los filtros aplicados.
                                        </td>
                                    </tr>
                                ) : (
                                    socios.data.map((socio) => {
                                        const tieneActiva = Boolean(socio.membresia_activa);
                                        const planNombre =
                                            socio.membresia_activa?.plan?.nombre ||
                                            socio.ultima_membresia?.plan?.nombre ||
                                            'Sin Membresía';
                                        const fechaFin =
                                            socio.membresia_activa?.fecha_fin ||
                                            socio.ultima_membresia?.fecha_fin;

                                        return (
                                            <tr key={socio.id} className="hover:bg-neutral-50/60 dark:hover:bg-neutral-800/40">
                                                <td className="py-3 px-4 font-mono font-semibold text-xs text-neutral-900 dark:text-white">
                                                    <Link href={route('socios.show', socio.id)} className="text-lime-500 hover:underline">
                                                        {socio.codigo}
                                                    </Link>
                                                </td>
                                                <td className="py-3 px-4 font-medium text-neutral-900 dark:text-white">
                                                    <Link href={route('socios.show', socio.id)} className="hover:text-lime-500 transition font-semibold">
                                                        {socio.nombre} {socio.apellido}
                                                    </Link>
                                                </td>
                                                <td className="py-3 px-4 text-xs">
                                                    <div className="font-mono font-medium text-neutral-800 dark:text-neutral-200">{socio.dni}</div>
                                                    {socio.fecha_nacimiento && (
                                                        <div className="text-[11px] text-neutral-400 font-mono mt-0.5">
                                                            Nac: {socio.fecha_nacimiento.split('T')[0]}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4 text-xs">
                                                    <div>{socio.correo}</div>
                                                    <div className="text-neutral-400">{socio.telefono || '-'}</div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <span
                                                        className={`inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold capitalize ${
                                                            socio.estado === 'activo'
                                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                                : socio.estado === 'moroso'
                                                                  ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'
                                                                  : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'
                                                        }`}
                                                    >
                                                        {socio.estado}
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4 text-xs">
                                                    <div className="font-medium text-neutral-800 dark:text-neutral-200">
                                                        {planNombre}
                                                    </div>
                                                    {fechaFin ? (
                                                        <div
                                                            className={`flex items-center gap-1 text-[11px] ${
                                                                tieneActiva
                                                                    ? 'text-emerald-600 dark:text-emerald-400'
                                                                    : 'text-rose-600 dark:text-rose-400 font-semibold'
                                                            }`}
                                                        >
                                                            {tieneActiva ? (
                                                                <>
                                                                    <CheckCircle2 className="h-3 w-3" />
                                                                    Vence: {fechaFin}
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <AlertCircle className="h-3 w-3" />
                                                                    Expiró: {fechaFin}
                                                                </>
                                                            )}
                                                        </div>
                                                    ) : (
                                                        <div className="text-neutral-400 text-[11px]">Sin cuota registrada</div>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        <Link
                                                            href={route('socios.show', socio.id)}
                                                            className="rounded-md bg-neutral-100 p-1.5 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 transition"
                                                            title="Ver Expediente / Indagar"
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Link>
                                                        <Link
                                                            href={route('socios.edit', socio.id)}
                                                            className="rounded-md bg-blue-50 p-1.5 text-blue-600 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-400 transition"
                                                            title="Editar Datos"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Link>
                                                        <button
                                                            type="button"
                                                            onClick={() => setSocioParaRenovar(socio)}
                                                            className="rounded-md bg-emerald-50 p-1.5 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-400 transition"
                                                            title="Renovar / Asignar Membresía"
                                                        >
                                                            <CreditCard className="h-4 w-4" />
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleEliminarSocio(socio.id, `${socio.nombre} ${socio.apellido}`)}
                                                            className="rounded-md bg-rose-50 p-1.5 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-400 transition"
                                                            title="Dar de Baja"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
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

                    {/* Versión Móvil: Lista de Tarjetas Apiladas (block md:hidden) */}
                    <div className="block md:hidden divide-y divide-neutral-100 dark:divide-neutral-800/80">
                        {socios.data.length === 0 ? (
                            <div className="p-6 text-center text-sm text-neutral-500">
                                No se encontraron socios registrados con los filtros aplicados.
                            </div>
                        ) : (
                            socios.data.map((socio) => {
                                const tieneActiva = Boolean(socio.membresia_activa);
                                const planNombre =
                                    socio.membresia_activa?.plan?.nombre ||
                                    socio.ultima_membresia?.plan?.nombre ||
                                    'Sin Membresía';
                                const fechaFin =
                                    socio.membresia_activa?.fecha_fin ||
                                    socio.ultima_membresia?.fecha_fin;

                                return (
                                    <div key={socio.id} className="p-4 space-y-3">
                                        <div className="flex items-start justify-between gap-2">
                                            <Link
                                                href={route('socios.show', socio.id)}
                                                className="flex items-center gap-3 group"
                                            >
                                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-lime-400/10 text-lime-400 border border-lime-400/30 font-black text-sm uppercase group-hover:scale-105 transition">
                                                    {socio.nombre.charAt(0)}{socio.apellido.charAt(0)}
                                                </div>
                                                <div>
                                                    <h3 className="font-bold text-sm text-neutral-900 dark:text-white leading-tight group-hover:text-emerald-500 transition">
                                                        {socio.nombre} {socio.apellido}
                                                    </h3>
                                                    <span className="font-mono text-xs text-neutral-400">
                                                        {socio.codigo} • DNI: {socio.dni}
                                                        {socio.fecha_nacimiento ? ` • Nac: ${socio.fecha_nacimiento.split('T')[0]}` : ''}
                                                    </span>
                                                </div>
                                            </Link>

                                            <span
                                                className={`inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-bold capitalize shrink-0 ${
                                                    socio.estado === 'activo'
                                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                        : socio.estado === 'moroso'
                                                          ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'
                                                          : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'
                                                }`}
                                            >
                                                {socio.estado}
                                            </span>
                                        </div>

                                        {/* Info de contacto y plan */}
                                        <div className="rounded-xl bg-neutral-50 dark:bg-neutral-800/50 p-3 text-xs space-y-1.5 border border-neutral-100 dark:border-neutral-800/80">
                                            <div className="flex items-center justify-between text-neutral-600 dark:text-neutral-300">
                                                <span className="text-neutral-400">Plan Actual:</span>
                                                <span className="font-bold text-neutral-900 dark:text-white">{planNombre}</span>
                                            </div>

                                            <div className="flex items-center justify-between">
                                                <span className="text-neutral-400">Vencimiento:</span>
                                                {fechaFin ? (
                                                    <span
                                                        className={`font-semibold flex items-center gap-1 ${
                                                            tieneActiva
                                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                                : 'text-rose-600 dark:text-rose-400'
                                                        }`}
                                                    >
                                                        {tieneActiva ? <CheckCircle2 className="h-3 w-3" /> : <AlertCircle className="h-3 w-3" />}
                                                        {fechaFin}
                                                    </span>
                                                ) : (
                                                    <span className="text-neutral-500">Sin cuota</span>
                                                )}
                                            </div>

                                            <div className="flex items-center justify-between pt-1 border-t border-neutral-200/60 dark:border-neutral-700/60 text-neutral-400 text-[11px]">
                                                <span>{socio.correo}</span>
                                                {socio.telefono && (
                                                    <a href={`tel:${socio.telefono}`} className="text-lime-500 hover:underline">
                                                        {socio.telefono}
                                                    </a>
                                                )}
                                            </div>
                                        </div>

                                        {/* Botones de acción táctiles (Touch UX min-h-[44px]) */}
                                        <div className="grid grid-cols-2 gap-2 pt-1">
                                            <Link
                                                href={route('socios.show', socio.id)}
                                                className="flex items-center justify-center gap-1.5 min-h-[44px] rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 font-bold text-xs active:scale-98 transition"
                                            >
                                                <Eye className="h-4 w-4 text-emerald-500" />
                                                Expediente
                                            </Link>
                                            <Link
                                                href={route('socios.edit', socio.id)}
                                                className="flex items-center justify-center gap-1.5 min-h-[44px] rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-600 dark:text-blue-400 font-bold text-xs active:scale-98 transition"
                                            >
                                                <Edit className="h-4 w-4" />
                                                Editar
                                            </Link>
                                            <button
                                                type="button"
                                                onClick={() => setSocioParaRenovar(socio)}
                                                className="flex items-center justify-center gap-1.5 min-h-[44px] rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 font-bold text-xs active:scale-98 transition"
                                            >
                                                <CreditCard className="h-4 w-4" />
                                                Renovar Plan
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => handleEliminarSocio(socio.id, `${socio.nombre} ${socio.apellido}`)}
                                                className="flex items-center justify-center gap-1.5 min-h-[44px] rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 font-bold text-xs active:scale-98 transition"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                                Dar de Baja
                                            </button>
                                        </div>
                                    </div>
                                );
                            })
                        )}
                    </div>

                    {/* Barra de Navegación Paginada Compacta (1-50 de 5.042 < >) */}
                    <div className="border-t border-neutral-100 dark:border-neutral-800/80 px-4 py-3 bg-neutral-50/50 dark:bg-neutral-900/50 flex items-center justify-end">
                        <PaginadorCompacto paginacion={socios} />
                    </div>
                </div>

                {/* Modal Crear Nuevo Socio */}
                {modalCrearAbierto && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 sm:p-4 backdrop-blur-xs">
                        <div className="w-[95%] max-w-lg md:max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                            <div className="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                                <h3 className="text-base sm:text-lg font-bold text-neutral-900 dark:text-white flex items-center gap-2">
                                    <Plus className="h-5 w-5 text-emerald-600" />
                                    Registrar Nuevo Socio
                                </h3>
                                <button
                                    type="button"
                                    onClick={() => setModalCrearAbierto(false)}
                                    className="p-1.5 text-neutral-400 hover:text-neutral-600 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    ✕
                                </button>
                            </div>

                            <form onSubmit={handleCrearSocio} className="mt-4 space-y-3.5">
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                            Nombre *
                                        </label>
                                        <input
                                            type="text"
                                            required
                                            value={nuevoSocio.nombre}
                                            onChange={(e) => setNuevoSocio({ ...nuevoSocio, nombre: e.target.value })}
                                            className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                            placeholder="Ej. Lucas"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                            Apellido *
                                        </label>
                                        <input
                                            type="text"
                                            required
                                            value={nuevoSocio.apellido}
                                            onChange={(e) => setNuevoSocio({ ...nuevoSocio, apellido: e.target.value })}
                                            className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                            placeholder="Ej. Silva"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                            DNI / Cédula *
                                        </label>
                                        <input
                                            type="text"
                                            required
                                            value={nuevoSocio.dni}
                                            onChange={(e) => setNuevoSocio({ ...nuevoSocio, dni: e.target.value })}
                                            className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                            placeholder="Ej. 28990112"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                            Teléfono
                                        </label>
                                        <input
                                            type="text"
                                            value={nuevoSocio.telefono}
                                            onChange={(e) => setNuevoSocio({ ...nuevoSocio, telefono: e.target.value })}
                                            className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                            placeholder="+58 412 0000000"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Correo Electrónico *
                                    </label>
                                    <input
                                        type="email"
                                        required
                                        value={nuevoSocio.correo}
                                        onChange={(e) => setNuevoSocio({ ...nuevoSocio, correo: e.target.value })}
                                        className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                        placeholder="socio@correo.com"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Fecha de Nacimiento
                                    </label>
                                    <input
                                        type="date"
                                        value={nuevoSocio.fecha_nacimiento}
                                        onChange={(e) => setNuevoSocio({ ...nuevoSocio, fecha_nacimiento: e.target.value })}
                                        className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                    />
                                </div>

                                <div className="pt-2 border-t border-neutral-100 dark:border-neutral-800">
                                    <label className="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-1">
                                        Asignar Membresía Inicial (Opcional)
                                    </label>
                                    <select
                                        value={nuevoSocio.plan_id}
                                        onChange={(e) => setNuevoSocio({ ...nuevoSocio, plan_id: e.target.value })}
                                        className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                    >
                                        <option value="">Sin membresía inicial</option>
                                        {planes.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nombre} - ${p.precio} ({p.duracion_dias} días)
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-neutral-100 dark:border-neutral-800">
                                    <button
                                        type="button"
                                        onClick={() => setModalCrearAbierto(false)}
                                        className="min-h-[44px] rounded-xl px-4 py-2 text-xs font-medium text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={guardandoSocio}
                                        className="min-h-[44px] rounded-xl bg-emerald-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition disabled:opacity-50 flex items-center justify-center"
                                    >
                                        {guardandoSocio ? 'Guardando...' : 'Guardar y Generar Código'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                {/* Modal de Renovación */}
                {socioParaRenovar && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 sm:p-4 backdrop-blur-xs">
                        <div className="w-[95%] max-w-md max-h-[90vh] overflow-y-auto rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                            <div className="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                                <h3 className="text-base sm:text-lg font-bold text-neutral-900 dark:text-white flex items-center gap-2">
                                    <CreditCard className="h-5 w-5 text-emerald-600" />
                                    Renovar Membresía
                                </h3>
                                <button
                                    type="button"
                                    onClick={() => setSocioParaRenovar(null)}
                                    className="p-1.5 text-neutral-400 hover:text-neutral-600 rounded-lg"
                                >
                                    ✕
                                </button>
                            </div>

                            <form onSubmit={handleRenovarCuota} className="mt-4 space-y-4">
                                <div className="rounded-xl bg-neutral-50 dark:bg-neutral-800/60 p-3">
                                    <span className="text-[11px] text-neutral-500 uppercase font-bold block">Socio Seleccionado</span>
                                    <p className="font-bold text-sm text-neutral-900 dark:text-white">
                                        {socioParaRenovar.nombre} {socioParaRenovar.apellido}
                                    </p>
                                    <p className="text-xs font-mono text-neutral-400">
                                        Código: {socioParaRenovar.codigo} | DNI: {socioParaRenovar.dni}
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
                                        Plan de Membresía
                                    </label>
                                    <select
                                        value={planRenovacion}
                                        onChange={(e) => setPlanRenovacion(Number(e.target.value))}
                                        className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                    >
                                        {planes.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nombre} - ${p.precio} ({p.duracion_dias} días)
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
                                        Método de Pago
                                    </label>
                                    <select
                                        value={metodoPagoRenovacion}
                                        onChange={(e) => setMetodoPagoRenovacion(e.target.value)}
                                        className="w-full min-h-[44px] rounded-xl border border-neutral-300 px-3 text-base sm:text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                    >
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta de Débito / Crédito</option>
                                        <option value="transferencia">Transferencia Bancaria</option>
                                    </select>
                                </div>

                                <div className="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-neutral-100 dark:border-neutral-800">
                                    <button
                                        type="button"
                                        onClick={() => setSocioParaRenovar(null)}
                                        className="min-h-[44px] rounded-xl px-4 py-2 text-xs font-medium text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={renovando}
                                        className="min-h-[44px] rounded-xl bg-emerald-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition disabled:opacity-50 flex items-center justify-center"
                                    >
                                        {renovando ? 'Procesando...' : 'Confirmar y Activar Cuota'}
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


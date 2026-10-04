import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    Calendar,
    CheckCircle2,
    Clock,
    CreditCard,
    Dumbbell,
    Edit,
    FileText,
    Mail,
    MapPin,
    Phone,
    Plus,
    QrCode,
    Shield,
    Trash2,
    UserCheck,
    Users,
    XCircle,
    Zap,
} from 'lucide-react';
import React, { useState } from 'react';

interface SocioShowProps {
    socio: {
        id: number;
        codigo: string;
        nombre: string;
        apellido: string;
        dni: string;
        correo: string;
        telefono?: string;
        direccion?: string;
        fecha_nacimiento?: string;
        foto?: string;
        estado: string;
        observaciones_medicas?: string;
        created_at: string;
        user?: {
            id: number;
            email: string;
            sucursal?: {
                id: number;
                nombre: string;
                ciudad: string;
            };
        };
        membresia_activa?: {
            id: number;
            plan_id: number;
            plan?: {
                nombre: string;
                precio: number;
                duracion_dias: number;
            };
            precio_pagado: number;
            fecha_inicio: string;
            fecha_fin: string;
            cuotas_totales: number;
            cuotas_pagadas: number;
            saldo_pendiente: number;
            estado: string;
            metodo_pago: string;
        };
        membresias?: Array<{
            id: number;
            plan?: { nombre: string; precio: number };
            precio_pagado: number;
            fecha_inicio: string;
            fecha_fin: string;
            estado: string;
            metodo_pago: string;
        }>;
        rutinas?: Array<{
            id: number;
            nombre: string;
            nivel: string;
            nivel_dificultad: string;
            estado: string;
            activa: boolean;
            fecha_inicio?: string;
            dias_semana?: string[];
            ejercicios?: Array<{ id: number; nombre: string; grupo_muscular: string }>;
        }>;
        asistencias?: Array<{
            id: number;
            fecha_hora_entrada?: string;
            fecha_hora_salida?: string;
            fecha_hora: string;
            tiempo_permanencia_minutos?: number;
            metodo: string;
            estado_acceso: string;
            mensaje_acceso: string;
        }>;
    };
    planes: Array<{
        id: number;
        nombre: string;
        precio: number | string;
        duracion_dias: number;
    }>;
    navegacion: {
        anterior?: { id: number; nombre: string; apellido: string; codigo: string } | null;
        siguiente?: { id: number; nombre: string; apellido: string; codigo: string } | null;
    };
}

export default function SocioShow({ socio, planes = [], navegacion }: SocioShowProps) {
    const [modalRenovarAbierto, setModalRenovarAbierto] = useState(false);
    const [planIdSeleccionado, setPlanIdSeleccionado] = useState<number>(planes[0]?.id || 1);
    const [metodoPago, setMetodoPago] = useState('efectivo');
    const [procesandoRenovacion, setProcesandoRenovacion] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Socios y Membresías', href: '/socios' },
        { title: `${socio.nombre} ${socio.apellido}`, href: `/socios/${socio.id}` },
    ];

    const handleRenovarMembresia = (e: React.FormEvent) => {
        e.preventDefault();
        setProcesandoRenovacion(true);
        router.post(
            route('membresias.store'),
            {
                socio_id: socio.id,
                plan_id: planIdSeleccionado,
                metodo_pago: metodoPago,
            },
            {
                onSuccess: () => {
                    setModalRenovarAbierto(false);
                    setProcesandoRenovacion(false);
                },
                onError: () => setProcesandoRenovacion(false),
            }
        );
    };

    const handleDarDeBaja = () => {
        if (confirm(`¿Estás seguro de dar de baja al socio ${socio.nombre} ${socio.apellido}?`)) {
            router.delete(route('socios.destroy', socio.id));
        }
    };

    const tieneActiva = Boolean(socio.membresia_activa);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Expediente: ${socio.nombre} ${socio.apellido} - FitLife`} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
                {/* Barra Superior de Indagación Rápida entre Socios */}
                <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-neutral-900 border border-neutral-800 rounded-2xl p-3 px-4 shadow-sm">
                    <Link
                        href={route('socios.index')}
                        className="inline-flex items-center gap-2 text-xs font-semibold text-neutral-400 hover:text-white transition"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        <span>Volver al Catálogo</span>
                    </Link>

                    {/* Navegación de Indagación Continua */}
                    <div className="flex items-center justify-end gap-2 text-xs">
                        <span className="text-neutral-500 font-mono hidden sm:inline mr-2">Indagación de Socios:</span>
                        {navegacion.anterior ? (
                            <Link
                                href={route('socios.show', navegacion.anterior.id)}
                                className="inline-flex items-center gap-1.5 rounded-xl border border-neutral-700 bg-neutral-800/80 px-3 py-1.5 font-medium text-neutral-200 hover:bg-neutral-700 hover:text-white transition"
                                title={`Ir a socio anterior: ${navegacion.anterior.nombre} ${navegacion.anterior.apellido}`}
                            >
                                <ArrowLeft className="h-3.5 w-3.5" />
                                <span className="max-w-[120px] truncate">{navegacion.anterior.nombre}</span>
                            </Link>
                        ) : (
                            <span className="rounded-xl border border-neutral-800 bg-neutral-900/50 px-3 py-1.5 text-neutral-600 opacity-50 cursor-not-allowed">
                                Anterior
                            </span>
                        )}

                        {navegacion.siguiente ? (
                            <Link
                                href={route('socios.show', navegacion.siguiente.id)}
                                className="inline-flex items-center gap-1.5 rounded-xl border border-lime-500/40 bg-lime-500/10 px-3 py-1.5 font-semibold text-lime-400 hover:bg-lime-500/20 transition"
                                title={`Ir a socio siguiente: ${navegacion.siguiente.nombre} ${navegacion.siguiente.apellido}`}
                            >
                                <span className="max-w-[120px] truncate">{navegacion.siguiente.nombre}</span>
                                <ArrowRight className="h-3.5 w-3.5" />
                            </Link>
                        ) : (
                            <span className="rounded-xl border border-neutral-800 bg-neutral-900/50 px-3 py-1.5 text-neutral-600 opacity-50 cursor-not-allowed">
                                Siguiente
                            </span>
                        )}
                    </div>
                </div>

                {/* Hero / Ficha Principal del Socio */}
                <div className="relative overflow-hidden rounded-3xl border border-neutral-800 bg-gradient-to-br from-neutral-900 via-neutral-900/90 to-neutral-950 p-6 md:p-8 shadow-xl">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div className="flex items-start md:items-center gap-5">
                            <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-lime-500 to-emerald-400 text-neutral-950 font-black text-2xl shadow-lg border-2 border-lime-300">
                                {socio.nombre.charAt(0)}{socio.apellido.charAt(0)}
                            </div>

                            <div className="space-y-1.5">
                                <div className="flex flex-wrap items-center gap-2.5">
                                    <h1 className="text-2xl md:text-3xl font-black tracking-tight text-white">
                                        {socio.nombre} {socio.apellido}
                                    </h1>
                                    <span
                                        className={`inline-flex items-center gap-1 rounded-full px-3 py-0.5 text-xs font-bold uppercase tracking-wider ${
                                            socio.estado === 'activo'
                                                ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                                : socio.estado === 'moroso'
                                                  ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                                                  : 'bg-neutral-800 text-neutral-400'
                                        }`}
                                    >
                                        {socio.estado}
                                    </span>

                                    {socio.user?.sucursal && (
                                        <span className="inline-flex items-center gap-1 rounded-full bg-neutral-800/90 border border-neutral-700 px-3 py-0.5 text-xs font-medium text-neutral-300">
                                            <MapPin className="h-3 w-3 text-lime-400" />
                                            {socio.user.sucursal.nombre}
                                        </span>
                                    )}
                                </div>

                                <div className="flex flex-wrap items-center gap-4 text-xs font-mono text-neutral-400">
                                    <span className="bg-neutral-800 px-2 py-0.5 rounded text-neutral-300 font-semibold">{socio.codigo}</span>
                                    <span>DNI: {socio.dni}</span>
                                    <span>Alta: {new Date(socio.created_at).toLocaleDateString('es-ES')}</span>
                                </div>
                            </div>
                        </div>

                        {/* Botones de Acción Rápida */}
                        <div className="flex flex-wrap items-center gap-2.5">
                            <Link
                                href={route('socios.edit', socio.id)}
                                className="inline-flex items-center gap-2 rounded-xl border border-neutral-700 bg-neutral-800 px-4 py-2 text-xs font-semibold text-neutral-200 hover:bg-neutral-700 hover:text-white transition"
                            >
                                <Edit className="h-4 w-4 text-neutral-400" />
                                <span>Editar Socio</span>
                            </Link>

                            <button
                                type="button"
                                onClick={() => setModalRenovarAbierto(true)}
                                className="inline-flex items-center gap-2 rounded-xl border border-lime-500/40 bg-lime-500 px-4 py-2 text-xs font-bold text-neutral-950 hover:bg-lime-400 transition shadow-md shadow-lime-950/40"
                            >
                                <CreditCard className="h-4 w-4" />
                                <span>Renovar Membresía</span>
                            </button>

                            <button
                                type="button"
                                onClick={handleDarDeBaja}
                                className="inline-flex items-center gap-2 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3.5 py-2 text-xs font-semibold text-rose-400 hover:bg-rose-500/20 transition"
                                title="Dar de baja / Eliminar"
                            >
                                <Trash2 className="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                {/* Grid de Contenido en 3 Columnas */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Columna 1: Información de Contacto y Salud */}
                    <div className="space-y-6">
                        <div className="rounded-3xl border border-neutral-800 bg-neutral-900/60 p-6 shadow-sm">
                            <h2 className="text-base font-bold text-white flex items-center gap-2 mb-4">
                                <Users className="h-4 w-4 text-lime-400" />
                                Datos Personales
                            </h2>

                            <div className="space-y-3.5 text-xs">
                                <div>
                                    <span className="text-neutral-500 block">Correo Electrónico</span>
                                    <span className="text-neutral-200 font-medium flex items-center gap-1.5 mt-0.5">
                                        <Mail className="h-3.5 w-3.5 text-neutral-400" />
                                        {socio.correo}
                                    </span>
                                </div>

                                <div>
                                    <span className="text-neutral-500 block">Teléfono / Celular</span>
                                    <span className="text-neutral-200 font-medium flex items-center gap-1.5 mt-0.5">
                                        <Phone className="h-3.5 w-3.5 text-neutral-400" />
                                        {socio.telefono || 'No registrado'}
                                    </span>
                                </div>

                                <div>
                                    <span className="text-neutral-500 block">Dirección de Residencia</span>
                                    <span className="text-neutral-200 font-medium flex items-center gap-1.5 mt-0.5">
                                        <MapPin className="h-3.5 w-3.5 text-neutral-400" />
                                        {socio.direccion || 'No especificada'}
                                    </span>
                                </div>

                                <div>
                                    <span className="text-neutral-500 block">Fecha de Nacimiento</span>
                                    <span className="text-neutral-200 font-medium flex items-center gap-1.5 mt-0.5 font-mono text-xs">
                                        <Calendar className="h-3.5 w-3.5 text-neutral-400" />
                                        {socio.fecha_nacimiento ? (socio.fecha_nacimiento.includes('T') ? socio.fecha_nacimiento.split('T')[0] : socio.fecha_nacimiento) : 'No registrada'}
                                    </span>
                                </div>

                                <div className="pt-2 border-t border-neutral-800/80">
                                    <span className="text-neutral-500 block mb-1">Observaciones Médicas</span>
                                    <p className="rounded-xl border border-neutral-800 bg-neutral-950/60 p-3 text-neutral-300 italic text-[11px]">
                                        {socio.observaciones_medicas || 'Apto para todo tipo de actividad deportiva. Sin restricciones reportadas.'}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Tarjeta de Membresía Actual */}
                        <div className="rounded-3xl border border-neutral-800 bg-neutral-900/60 p-6 shadow-sm">
                            <h2 className="text-base font-bold text-white flex items-center gap-2 mb-4">
                                <Shield className="h-4 w-4 text-lime-400" />
                                Membresía Activa
                            </h2>

                            {socio.membresia_activa ? (
                                <div className="space-y-4">
                                    <div className="rounded-2xl border border-lime-500/30 bg-lime-500/10 p-4">
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs font-bold uppercase text-lime-400 tracking-wider">
                                                {socio.membresia_activa.plan?.nombre}
                                            </span>
                                            <span className="text-lg font-black text-white font-mono">
                                                ${Number(socio.membresia_activa.precio_pagado).toFixed(2)}
                                            </span>
                                        </div>
                                        <div className="mt-2 text-xs text-neutral-300 flex items-center gap-1.5">
                                            <Clock className="h-3.5 w-3.5 text-lime-400" />
                                            <span>Vence el {socio.membresia_activa.fecha_fin}</span>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 text-xs">
                                        <div className="p-3 rounded-xl bg-neutral-800/40 border border-neutral-800">
                                            <span className="text-neutral-500 block text-[11px]">Cuotas</span>
                                            <span className="font-bold text-white">
                                                {socio.membresia_activa.cuotas_pagadas || 1} de {socio.membresia_activa.cuotas_totales || 1}
                                            </span>
                                        </div>
                                        <div className="p-3 rounded-xl bg-neutral-800/40 border border-neutral-800">
                                            <span className="text-neutral-500 block text-[11px]">Método</span>
                                            <span className="font-bold text-white capitalize">{socio.membresia_activa.metodo_pago}</span>
                                        </div>
                                    </div>
                                </div>
                            ) : (
                                <div className="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-center">
                                    <AlertCircle className="h-8 w-8 text-rose-400 mx-auto mb-2" />
                                    <p className="text-xs font-semibold text-rose-300">El socio no tiene una membresía activa.</p>
                                    <button
                                        type="button"
                                        onClick={() => setModalRenovarAbierto(true)}
                                        className="mt-3 text-xs font-bold text-lime-400 underline hover:text-lime-300"
                                    >
                                        + Asignar Membresía Ahora
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Columna 2 y 3: Asistencias y Rutinas del Socio */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Historial de Asistencias */}
                        <div className="rounded-3xl border border-neutral-800 bg-neutral-900/60 p-6 shadow-sm">
                            <div className="flex items-center justify-between mb-4">
                                <h2 className="text-base font-bold text-white flex items-center gap-2">
                                    <QrCode className="h-4 w-4 text-lime-400" />
                                    Histórico de Asistencias Recientes
                                </h2>
                                <Link
                                    href={`/asistencias?buscar=${encodeURIComponent(socio.codigo)}`}
                                    className="text-xs font-semibold text-lime-400 hover:underline"
                                >
                                    Ver en Portería →
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs text-neutral-300">
                                    <thead className="border-b border-neutral-800 bg-neutral-800/40 text-[11px] uppercase font-semibold text-neutral-400">
                                        <tr>
                                            <th className="py-2.5 px-3">Fecha</th>
                                            <th className="py-2.5 px-3">Entrada</th>
                                            <th className="py-2.5 px-3">Salida</th>
                                            <th className="py-2.5 px-3">Permanencia</th>
                                            <th className="py-2.5 px-3">Resultado</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-neutral-800/60">
                                        {!socio.asistencias || socio.asistencias.length === 0 ? (
                                            <tr>
                                                <td colSpan={5} className="py-6 text-center text-neutral-500">
                                                    Sin marcajes de asistencia registrados para este socio.
                                                </td>
                                            </tr>
                                        ) : (
                                            socio.asistencias.map((asist) => {
                                                const permitido = asist.estado_acceso === 'permitido';
                                                return (
                                                    <tr key={asist.id} className="hover:bg-neutral-800/30">
                                                        <td className="py-2.5 px-3 font-mono text-neutral-400">
                                                            {new Date(asist.fecha_hora_entrada || asist.fecha_hora).toLocaleDateString('es-ES')}
                                                        </td>
                                                        <td className="py-2.5 px-3 font-mono text-neutral-200">
                                                            {new Date(asist.fecha_hora_entrada || asist.fecha_hora).toLocaleTimeString('es-ES', {
                                                                hour: '2-digit',
                                                                minute: '2-digit',
                                                            })}
                                                        </td>
                                                        <td className="py-2.5 px-3 font-mono text-neutral-200">
                                                            {asist.fecha_hora_salida
                                                                ? new Date(asist.fecha_hora_salida).toLocaleTimeString('es-ES', {
                                                                      hour: '2-digit',
                                                                      minute: '2-digit',
                                                                  })
                                                                : <span className="text-lime-400 font-semibold">En sala</span>}
                                                        </td>
                                                        <td className="py-2.5 px-3 font-mono">
                                                            {asist.tiempo_permanencia_minutos
                                                                ? `${asist.tiempo_permanencia_minutos} min`
                                                                : '-'}
                                                        </td>
                                                        <td className="py-2.5 px-3">
                                                            <span
                                                                className={`inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold uppercase ${
                                                                    permitido
                                                                        ? 'bg-emerald-500/10 text-emerald-400'
                                                                        : 'bg-rose-500/10 text-rose-400'
                                                                }`}
                                                            >
                                                                {permitido ? 'Acceso OK' : 'Denegado'}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {/* Rutinas Asignadas */}
                        <div className="rounded-3xl border border-neutral-800 bg-neutral-900/60 p-6 shadow-sm">
                            <div className="flex items-center justify-between mb-4">
                                <h2 className="text-base font-bold text-white flex items-center gap-2">
                                    <Dumbbell className="h-4 w-4 text-lime-400" />
                                    Rutinas y Programación de Entrenamiento
                                </h2>
                                <Link href="/rutinas" className="text-xs font-semibold text-lime-400 hover:underline">
                                    Ver en Rutinas →
                                </Link>
                            </div>

                            <div className="space-y-3">
                                {!socio.rutinas || socio.rutinas.length === 0 ? (
                                    <div className="p-6 text-center rounded-2xl border border-dashed border-neutral-800 text-xs text-neutral-500">
                                        Este socio aún no tiene rutinas asignadas.
                                    </div>
                                ) : (
                                    socio.rutinas.map((rutina) => (
                                        <div
                                            key={rutina.id}
                                            className="p-4 rounded-2xl border border-neutral-800 bg-neutral-900/90 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                                        >
                                            <div className="space-y-1">
                                                <div className="flex items-center gap-2">
                                                    <h3 className="font-bold text-sm text-white">{rutina.nombre}</h3>
                                                    <span
                                                        className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                                                            rutina.estado === 'Activa'
                                                                ? 'bg-lime-500/20 text-lime-400'
                                                                : 'bg-neutral-800 text-neutral-400'
                                                        }`}
                                                    >
                                                        {rutina.estado}
                                                    </span>
                                                    <span className="text-[10px] font-semibold bg-neutral-800 px-2 py-0.5 rounded text-neutral-300">
                                                        Nivel: {rutina.nivel_dificultad || rutina.nivel}
                                                    </span>
                                                </div>
                                                <div className="text-xs text-neutral-400 flex items-center gap-2">
                                                    <span>{rutina.ejercicios?.length || 0} ejercicios programados</span>
                                                    {rutina.dias_semana && (
                                                        <span>• {rutina.dias_semana.join(', ')}</span>
                                                    )}
                                                </div>
                                            </div>

                                            <Link
                                                href={`/rutinas/${rutina.id}`}
                                                className="inline-flex items-center gap-1.5 rounded-xl border border-neutral-700 px-3 py-1.5 text-xs font-semibold text-neutral-300 hover:bg-neutral-800 hover:text-white transition"
                                            >
                                                <span>Ver Rutina</span>
                                                <ArrowRight className="h-3 w-3" />
                                            </Link>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal de Renovación Rápida de Membresía */}
            {modalRenovarAbierto && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs p-4">
                    <div className="w-full max-w-md rounded-3xl border border-neutral-800 bg-neutral-900 p-6 shadow-2xl">
                        <h3 className="text-lg font-bold text-white mb-1">Renovar / Asignar Membresía</h3>
                        <p className="text-xs text-neutral-400 mb-4">
                            Selecciona el plan para {socio.nombre} {socio.apellido} ({socio.codigo}).
                        </p>

                        <form onSubmit={handleRenovarMembresia} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Plan de Membresía</label>
                                <select
                                    value={planIdSeleccionado}
                                    onChange={(e) => setPlanIdSeleccionado(Number(e.target.value))}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3 py-2 text-sm text-white"
                                >
                                    {planes.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            {p.nombre} — ${Number(p.precio).toFixed(2)} ({p.duracion_dias} días)
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Método de Pago</label>
                                <select
                                    value={metodoPago}
                                    onChange={(e) => setMetodoPago(e.target.value)}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3 py-2 text-sm text-white"
                                >
                                    <option value="efectivo">Efectivo (Recepción)</option>
                                    <option value="tarjeta">Tarjeta de Débito / Crédito</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                    <option value="pago_movil">Pago Móvil</option>
                                </select>
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-3 border-t border-neutral-800">
                                <button
                                    type="button"
                                    onClick={() => setModalRenovarAbierto(false)}
                                    className="rounded-xl px-4 py-2 text-xs font-semibold text-neutral-400 hover:text-white"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={procesandoRenovacion}
                                    className="rounded-xl bg-lime-500 px-4 py-2 text-xs font-bold text-neutral-950 hover:bg-lime-400 disabled:opacity-50"
                                >
                                    {procesandoRenovacion ? 'Registrando...' : 'Confirmar Suscripción'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}


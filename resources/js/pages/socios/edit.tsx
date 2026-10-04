import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Check, LoaderCircle, Save, Trash2, User } from 'lucide-react';
import React, { useState } from 'react';

interface SocioEditProps {
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
        estado: string;
        observaciones_medicas?: string;
        user?: {
            id: number;
            sucursal_id?: number;
        };
    };
    sucursales: Array<{
        id: number;
        nombre: string;
        ciudad: string;
    }>;
}

export default function SocioEdit({ socio, sucursales = [] }: SocioEditProps) {
    const [valores, setValores] = useState({
        nombre: socio.nombre,
        apellido: socio.apellido,
        dni: socio.dni,
        correo: socio.correo,
        telefono: socio.telefono || '',
        direccion: socio.direccion || '',
        fecha_nacimiento: socio.fecha_nacimiento ? (socio.fecha_nacimiento.includes('T') ? socio.fecha_nacimiento.split('T')[0] : socio.fecha_nacimiento) : '',
        estado: socio.estado,
        observaciones_medicas: socio.observaciones_medicas || '',
        sucursal_id: socio.user?.sucursal_id || sucursales[0]?.id || 1,
    });

    const [guardando, setGuardando] = useState(false);
    const [errores, setErrores] = useState<Record<string, string>>({});

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Socios', href: '/socios' },
        { title: `${socio.nombre} ${socio.apellido}`, href: `/socios/${socio.id}` },
        { title: 'Editar', href: `/socios/${socio.id}/edit` },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setGuardando(true);
        setErrores({});

        router.put(route('socios.update', socio.id), valores, {
            onSuccess: () => setGuardando(false),
            onError: (err) => {
                setErrores(err);
                setGuardando(false);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Editar Socio: ${socio.nombre} ${socio.apellido} - FitLife`} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
                <div className="flex items-center justify-between">
                    <Link
                        href={route('socios.show', socio.id)}
                        className="inline-flex items-center gap-2 text-xs font-semibold text-neutral-400 hover:text-white transition"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        <span>Volver a la Ficha del Socio</span>
                    </Link>

                    <span className="font-mono text-xs text-neutral-400 bg-neutral-800 px-2.5 py-1 rounded-lg">
                        Código: {socio.codigo}
                    </span>
                </div>

                <div className="rounded-3xl border border-neutral-800 bg-neutral-900 p-6 md:p-8 shadow-xl">
                    <div className="flex items-center gap-3 pb-6 border-b border-neutral-800 mb-6">
                        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-lime-500/10 text-lime-400 border border-lime-500/20">
                            <User className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-xl font-bold text-white">Editar Datos del Socio</h1>
                            <p className="text-xs text-neutral-400">Actualiza la ficha técnica, contacto, sede y estado de membresía.</p>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Nombre</label>
                                <input
                                    type="text"
                                    required
                                    value={valores.nombre}
                                    onChange={(e) => setValores({ ...valores, nombre: e.target.value })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white"
                                />
                                {errores.nombre && <span className="text-xs text-rose-400 mt-1 block">{errores.nombre}</span>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Apellido</label>
                                <input
                                    type="text"
                                    required
                                    value={valores.apellido}
                                    onChange={(e) => setValores({ ...valores, apellido: e.target.value })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white"
                                />
                                {errores.apellido && <span className="text-xs text-rose-400 mt-1 block">{errores.apellido}</span>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Cédula / DNI</label>
                                <input
                                    type="text"
                                    required
                                    value={valores.dni}
                                    onChange={(e) => setValores({ ...valores, dni: e.target.value })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white font-mono"
                                />
                                {errores.dni && <span className="text-xs text-rose-400 mt-1 block">{errores.dni}</span>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Correo Electrónico</label>
                                <input
                                    type="email"
                                    required
                                    value={valores.correo}
                                    onChange={(e) => setValores({ ...valores, correo: e.target.value })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white"
                                />
                                {errores.correo && <span className="text-xs text-rose-400 mt-1 block">{errores.correo}</span>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Teléfono</label>
                                <input
                                    type="text"
                                    value={valores.telefono}
                                    onChange={(e) => setValores({ ...valores, telefono: e.target.value })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white"
                                    placeholder="+58 412..."
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Fecha de Nacimiento</label>
                                <input
                                    type="date"
                                    value={valores.fecha_nacimiento}
                                    onChange={(e) => setValores({ ...valores, fecha_nacimiento: e.target.value })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Estado del Socio</label>
                                <select
                                    value={valores.estado}
                                    onChange={(e) => setValores({ ...valores, estado: e.target.value })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white"
                                >
                                    <option value="activo">Activo</option>
                                    <option value="moroso">Moroso (Alerta / Cuotas impagas)</option>
                                    <option value="inactivo">Inactivo / Baja</option>
                                </select>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Sucursal / Sede Asignada</label>
                                <select
                                    value={valores.sucursal_id}
                                    onChange={(e) => setValores({ ...valores, sucursal_id: Number(e.target.value) })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white"
                                >
                                    {sucursales.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.nombre} ({s.ciudad})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-neutral-400 mb-1">Dirección de Domicilio</label>
                                <input
                                    type="text"
                                    value={valores.direccion}
                                    onChange={(e) => setValores({ ...valores, direccion: e.target.value })}
                                    className="w-full rounded-xl border border-neutral-700 bg-neutral-800 px-3.5 py-2.5 text-sm text-white"
                                />
                            </div>
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-neutral-400 mb-1">Observaciones Médicas o Deportivas</label>
                            <textarea
                                rows={3}
                                value={valores.observaciones_medicas}
                                onChange={(e) => setValores({ ...valores, observaciones_medicas: e.target.value })}
                                className="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-sm text-white"
                                placeholder="Afecciones, lesiones previas, autorizaciones médicas..."
                            />
                        </div>

                        <div className="flex items-center justify-end gap-3 pt-6 border-t border-neutral-800">
                            <Link
                                href={route('socios.show', socio.id)}
                                className="rounded-xl border border-neutral-700 px-4 py-2.5 text-xs font-semibold text-neutral-400 hover:text-white transition"
                            >
                                Cancelar
                            </Link>

                            <button
                                type="submit"
                                disabled={guardando}
                                className="inline-flex items-center gap-2 rounded-xl bg-lime-500 px-6 py-2.5 text-xs font-bold text-neutral-950 hover:bg-lime-400 transition disabled:opacity-50"
                            >
                                {guardando ? <LoaderCircle className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
                                <span>{guardando ? 'Guardando Cambios...' : 'Guardar Cambios'}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}


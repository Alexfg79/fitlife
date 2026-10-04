import {
    Check,
    Dumbbell,
    Layers,
    Plus,
    Sparkles,
    Trash2,
    X,
} from 'lucide-react';
import React, { useState } from 'react';

interface EjercicioItem {
    id: number;
    nombre: string;
    grupo_muscular: string;
    descripcion?: string;
    equipo_necesario?: string;
}

interface DrawerAsignarRutinaProps {
    abierto: boolean;
    onClose: () => void;
    ejercicios: EjercicioItem[];
    socios: Array<{ id: number; nombre: string; apellido: string; codigo: string }>;
    rutinaParaEditar?: {
        id: number;
        nombre: string;
        descripcion?: string;
        nivel: string;
        socio_id?: number | null;
        socio?: { id: number; nombre: string; apellido: string; codigo: string };
        ejercicios: Array<{
            id: number;
            nombre: string;
            grupo_muscular: string;
            pivot?: {
                orden?: number;
                series: number;
                repeticiones: string;
                descanso_segundos: number;
                notas?: string;
            };
        }>;
    } | null;
    onGuardar: (datos: {
        id?: number;
        nombre: string;
        descripcion?: string;
        nivel: string;
        socio_id?: number | null;
        ejercicios: Array<{
            ejercicio_id: number;
            series: number;
            repeticiones: string;
            descanso_segundos: number;
            notas?: string;
        }>;
    }) => void;
    guardando?: boolean;
}

export default function DrawerAsignarRutina({
    abierto,
    onClose,
    ejercicios = [],
    socios = [],
    rutinaParaEditar = null,
    onGuardar,
    guardando = false,
}: DrawerAsignarRutinaProps) {
    const [tabActiva, setTabActiva] = useState('pecho');
    const [nombre, setNombre] = useState('');
    const [descripcion, setDescripcion] = useState('');
    const [nivel, setNivel] = useState('principiante');
    const [socioId, setSocioId] = useState<string>('');

    const [ejerciciosConfigurados, setEjerciciosConfigurados] = useState<
        Array<{
            ejercicio_id: number;
            series: number;
            repeticiones: string;
            descanso_segundos: number;
            notas: string;
        }>
    >([]);

    React.useEffect(() => {
        if (rutinaParaEditar) {
            setNombre(rutinaParaEditar.nombre || '');
            setDescripcion(rutinaParaEditar.descripcion || '');
            setNivel(rutinaParaEditar.nivel || 'principiante');
            setSocioId(rutinaParaEditar.socio_id ? String(rutinaParaEditar.socio_id) : (rutinaParaEditar.socio?.id ? String(rutinaParaEditar.socio.id) : ''));
            if (rutinaParaEditar.ejercicios && rutinaParaEditar.ejercicios.length > 0) {
                setEjerciciosConfigurados(
                    rutinaParaEditar.ejercicios.map((ej) => ({
                        ejercicio_id: ej.id,
                        series: ej.pivot?.series ?? 3,
                        repeticiones: String(ej.pivot?.repeticiones ?? '10-12'),
                        descanso_segundos: ej.pivot?.descanso_segundos ?? 60,
                        notas: ej.pivot?.notas ?? '',
                    }))
                );
            } else {
                setEjerciciosConfigurados([]);
            }
        } else {
            setNombre('');
            setDescripcion('');
            setNivel('principiante');
            setSocioId('');
            setEjerciciosConfigurados([]);
        }
    }, [rutinaParaEditar, abierto]);

    if (!abierto) return null;

    const gruposMusculares = ['pecho', 'espalda', 'piernas', 'hombros', 'brazos', 'core', 'cardio'];

    const toggleEjercicio = (ejercicioId: number) => {
        const existe = ejerciciosConfigurados.find((e) => e.ejercicio_id === ejercicioId);
        if (existe) {
            setEjerciciosConfigurados(
                ejerciciosConfigurados.filter((e) => e.ejercicio_id !== ejercicioId)
            );
        } else {
            setEjerciciosConfigurados([
                ...ejerciciosConfigurados,
                {
                    ejercicio_id: ejercicioId,
                    series: 3,
                    repeticiones: '10-12',
                    descanso_segundos: 60,
                    notas: '',
                },
            ]);
        }
    };

    const actualizarCampo = (ejercicioId: number, campo: string, valor: any) => {
        setEjerciciosConfigurados(
            ejerciciosConfigurados.map((item) =>
                item.ejercicio_id === ejercicioId ? { ...item, [campo]: valor } : item
            )
        );
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (ejerciciosConfigurados.length === 0) {
            alert('Debes seleccionar al menos un ejercicio para la rutina.');
            return;
        }

        onGuardar({
            id: rutinaParaEditar?.id,
            nombre,
            descripcion,
            nivel,
            socio_id: socioId ? Number(socioId) : null,
            ejercicios: ejerciciosConfigurados,
        });
    };

    const ejerciciosFiltrados = ejercicios.filter((e) => e.grupo_muscular === tabActiva);

    return (
        <div className="fixed inset-0 z-50 overflow-hidden font-sans">
            {/* Backdrop con Blur Suave */}
            <div
                onClick={onClose}
                className="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity duration-300 animate-in fade-in"
            />

            {/* Panel Lateral Deslizante (Slide-Over Drawer) */}
            <div className="fixed inset-y-0 right-0 flex max-w-full pl-0 sm:pl-10">
                <aside className="w-screen max-w-xl border-l border-slate-800/90 bg-slate-950 p-4 sm:p-6 shadow-2xl flex flex-col justify-between animate-in slide-in-from-right duration-300 overflow-y-auto">
                    <div>
                        {/* Cabecera del Drawer */}
                        <div className="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-400/10 border border-lime-400/30 text-lime-400 shadow-[0_0_15px_rgba(132,204,22,0.3)]">
                                    <Dumbbell className="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 className="text-base sm:text-lg font-black tracking-tight text-white uppercase italic">
                                        {rutinaParaEditar ? 'Editar' : 'Diseñar'}{' '}
                                        <span className="text-lime-400 not-italic">Rutina</span>
                                    </h3>
                                    <p className="text-[11px] sm:text-xs text-slate-400">
                                        {rutinaParaEditar
                                            ? 'Modificar ejercicios, series y asignación'
                                            : 'Prescripción técnica y asignación personalizada'}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                onClick={onClose}
                                className="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition cursor-pointer"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        {/* Formulario de Parámetros Generales */}
                        <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Nombre de Rutina *
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={nombre}
                                    onChange={(e) => setNombre(e.target.value)}
                                    placeholder="Ej. Hipertrofia Tren Superior"
                                    className="w-full rounded-xl border border-slate-800 bg-slate-900/90 py-2 px-3 text-xs text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden"
                                />
                            </div>

                            <div>
                                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Asignar a Socio
                                </label>
                                <select
                                    value={socioId}
                                    onChange={(e) => setSocioId(e.target.value)}
                                    className="w-full rounded-xl border border-slate-800 bg-slate-900/90 py-2 px-3 text-xs text-white focus:border-lime-400 focus:outline-hidden"
                                >
                                    <option value="">Plantilla General</option>
                                    {socios.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.nombre} {s.apellido} ({s.codigo})
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="mt-2.5 grid grid-cols-2 gap-3">
                            <div>
                                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Nivel
                                </label>
                                <select
                                    value={nivel}
                                    onChange={(e) => setNivel(e.target.value)}
                                    className="w-full rounded-xl border border-slate-800 bg-slate-900/90 py-2 px-3 text-xs text-white focus:border-lime-400 focus:outline-hidden"
                                >
                                    <option value="principiante">Principiante</option>
                                    <option value="intermedio">Intermedio</option>
                                    <option value="avanzado">Avanzado</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Objetivo / Enfoque
                                </label>
                                <input
                                    type="text"
                                    value={descripcion}
                                    onChange={(e) => setDescripcion(e.target.value)}
                                    placeholder="Ej. Sobrecarga progresiva"
                                    className="w-full rounded-xl border border-slate-800 bg-slate-900/90 py-2 px-3 text-xs text-white placeholder-slate-500 focus:border-lime-400 focus:outline-hidden"
                                />
                            </div>
                        </div>

                        {/* Pestañas de Grupos Musculares */}
                        <div className="mt-5 border-t border-slate-800/80 pt-3">
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                    <Layers className="h-3.5 w-3.5 text-lime-400" />
                                    Catálogo de Ejercicios
                                </span>
                                <span className="font-mono text-[10px] text-lime-400 font-bold">
                                    {ejerciciosConfigurados.length} añadidos
                                </span>
                            </div>

                            <div className="flex gap-1.5 overflow-x-auto pb-1.5 scrollbar-none">
                                {gruposMusculares.map((grupo) => (
                                    <button
                                        key={grupo}
                                        type="button"
                                        onClick={() => setTabActiva(grupo)}
                                        className={`rounded-xl border px-3 py-1.5 text-xs font-bold uppercase tracking-wider transition whitespace-nowrap cursor-pointer ${
                                            tabActiva === grupo
                                                ? 'border-lime-400 bg-lime-400/10 text-lime-400 shadow-[0_0_12px_rgba(132,204,22,0.25)]'
                                                : 'border-slate-800 bg-slate-900/60 text-slate-400 hover:border-slate-700'
                                        }`}
                                    >
                                        {grupo}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Listado de Ejercicios del Grupo Seleccionado */}
                        <div className="mt-3 max-h-[36vh] space-y-2 overflow-y-auto pr-1">
                            {ejerciciosFiltrados.length === 0 ? (
                                <p className="text-center py-6 text-xs text-slate-500">
                                    No hay ejercicios disponibles para este grupo muscular.
                                </p>
                            ) : (
                                ejerciciosFiltrados.map((item) => {
                                    const configuracion = ejerciciosConfigurados.find(
                                        (e) => e.ejercicio_id === item.id
                                    );
                                    const seleccionado = Boolean(configuracion);

                                    return (
                                        <div
                                            key={item.id}
                                            className={`rounded-xl border p-3 transition-all duration-200 ${
                                                seleccionado
                                                    ? 'border-lime-400/80 bg-gradient-to-r from-lime-950/40 to-slate-900 shadow-[0_0_15px_rgba(132,204,22,0.12)]'
                                                    : 'border-slate-800/80 bg-slate-900/50 hover:border-slate-700'
                                            }`}
                                        >
                                            <div
                                                onClick={() => toggleEjercicio(item.id)}
                                                className="flex cursor-pointer items-center justify-between"
                                            >
                                                <div className="flex items-center gap-2.5">
                                                    <div
                                                        className={`flex h-5 w-5 shrink-0 items-center justify-center rounded-md border text-xs font-bold transition ${
                                                            seleccionado
                                                                ? 'border-lime-400 bg-lime-400 text-slate-950 shadow-[0_0_8px_rgba(132,204,22,0.6)]'
                                                                : 'border-slate-700 bg-slate-950 text-transparent'
                                                        }`}
                                                    >
                                                        <Check className="h-3.5 w-3.5 stroke-[3]" />
                                                    </div>
                                                    <div>
                                                        <h4 className="text-xs font-bold text-white leading-tight">
                                                            {item.nombre}
                                                        </h4>
                                                        <span className="text-[10px] text-slate-400">
                                                            {item.equipo_necesario || 'Peso corporal'}
                                                        </span>
                                                    </div>
                                                </div>

                                                <span className="rounded bg-slate-800 px-2 py-0.5 font-mono text-[10px] text-slate-300">
                                                    {seleccionado ? 'Añadido' : '+ Agregar'}
                                                </span>
                                            </div>

                                            {/* Parámetros de Series, Reps y Descanso si está seleccionado */}
                                            {seleccionado && configuracion && (
                                                <div className="mt-3 grid grid-cols-3 gap-2 border-t border-slate-800/80 pt-2.5">
                                                    <div>
                                                        <label className="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">
                                                            Series
                                                        </label>
                                                        <input
                                                            type="number"
                                                            min={1}
                                                            max={20}
                                                            value={configuracion.series}
                                                            onChange={(e) =>
                                                                actualizarCampo(item.id, 'series', Number(e.target.value))
                                                            }
                                                            className="w-full rounded-lg border border-slate-700 bg-slate-950 p-1 text-center text-xs text-white font-mono"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">
                                                            Reps
                                                        </label>
                                                        <input
                                                            type="text"
                                                            value={configuracion.repeticiones}
                                                            onChange={(e) =>
                                                                actualizarCampo(item.id, 'repeticiones', e.target.value)
                                                            }
                                                            className="w-full rounded-lg border border-slate-700 bg-slate-950 p-1 text-center text-xs text-white font-mono"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">
                                                            Descanso (s)
                                                        </label>
                                                        <input
                                                            type="number"
                                                            min={0}
                                                            max={600}
                                                            step={15}
                                                            value={configuracion.descanso_segundos}
                                                            onChange={(e) =>
                                                                actualizarCampo(
                                                                    item.id,
                                                                    'descanso_segundos',
                                                                    Number(e.target.value)
                                                                )
                                                            }
                                                            className="w-full rounded-lg border border-slate-700 bg-slate-950 p-1 text-center text-xs text-white font-mono"
                                                        />
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </div>

                    {/* Pie del Drawer: Confirmación y Envío */}
                    <div className="border-t border-slate-800/90 pt-4 flex items-center justify-between">
                        <span className="font-mono text-xs text-slate-400">
                            <strong className="text-lime-400 text-sm">{ejerciciosConfigurados.length}</strong> ejercicios
                        </span>

                        <div className="flex gap-2">
                            <button
                                type="button"
                                onClick={onClose}
                                className="rounded-xl px-4 py-2 text-xs font-bold text-slate-400 hover:bg-slate-900 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                onClick={handleSubmit}
                                disabled={guardando || ejerciciosConfigurados.length === 0 || !nombre.trim()}
                                className="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-lime-400 to-emerald-500 px-5 py-2.5 text-xs font-black uppercase tracking-wider text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.35)] hover:shadow-[0_0_30px_rgba(132,204,22,0.55)] transition disabled:opacity-40 cursor-pointer"
                            >
                                {guardando
                                    ? 'Guardando...'
                                    : rutinaParaEditar
                                      ? 'Actualizar Rutina'
                                      : 'Confirmar y Asignar Rutina'}
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    );
}


import { Head, Link, router } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    ArrowLeft,
    CheckCircle2,
    Clock,
    Delete,
    Dumbbell,
    Flame,
    KeyRound,
    LoaderCircle,
    LogOut,
    QrCode,
    Sparkles,
    UserCheck,
    Users,
    XCircle,
    Zap,
} from 'lucide-react';
import React, { useEffect, useState } from 'react';

interface ClaseItem {
    id: number;
    nombre: string;
    horario: string;
    sala: string;
    cupo_maximo: number;
    cupos_disponibles: number;
}

interface KioscoProps {
    clases: ClaseItem[];
}

export default function Kiosco({ clases = [] }: KioscoProps) {
    const [identificador, setIdentificador] = useState('');
    const [modo, setModo] = useState<'entrada' | 'salida'>('entrada');
    const [claseSeleccionada, setClaseSeleccionada] = useState<number | null>(null);
    const [procesando, setProcesando] = useState(false);
    const [resultado, setResultado] = useState<any>(null);
    const [conteoRegresivo, setConteoRegresivo] = useState<number | null>(null);

    // Auto-reinicio después de mostrar resultado
    useEffect(() => {
        if (!resultado) return;

        let tiempo = 6;
        setConteoRegresivo(tiempo);

        const intervalo = setInterval(() => {
            tiempo -= 1;
            setConteoRegresivo(tiempo);
            if (tiempo <= 0) {
                clearInterval(intervalo);
                limpiarPantalla();
            }
        }, 1000);

        return () => clearInterval(intervalo);
    }, [resultado]);

    const limpiarPantalla = () => {
        setIdentificador('');
        setResultado(null);
        setConteoRegresivo(null);
    };

    const presionarDigito = (digito: string) => {
        if (identificador.length < 15) {
            setIdentificador((prev) => prev + digito);
        }
    };

    const borrarDigito = () => {
        setIdentificador((prev) => prev.slice(0, -1));
    };

    const procesarMarcaje = async (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        const codigoFinal = identificador.trim();
        if (!codigoFinal || procesando) return;

        setProcesando(true);
        setResultado(null);

        try {
            if (modo === 'entrada') {
                const res = await fetch('/api/asistencias/escanear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        codigo: codigoFinal,
                        dni: codigoFinal,
                        metodo: 'kiosco',
                        tipo_acceso: claseSeleccionada ? 'clase_grupal' : 'general',
                        clase_id: claseSeleccionada,
                    }),
                });

                const data = await res.json();
                setResultado(data);
            } else {
                // Modo Salida (Check-out)
                const res = await fetch('/api/asistencias/check-out', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        codigo: codigoFinal,
                        dni: codigoFinal,
                    }),
                });

                const data = await res.json();
                setResultado(data);
            }
        } catch (error) {
            setResultado({
                exito: false,
                mensaje: 'Error de conexión con el servidor central de FitLife.',
            });
        } finally {
            setProcesando(false);
        }
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col justify-between p-4 sm:p-8 relative overflow-hidden">
            <Head title="Kiosco de Autoservicio • FitLife OS" />

            {/* Resplandor superior de ambiente deportivo */}
            <div className="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-lime-500/10 rounded-full blur-[140px] pointer-events-none" />

            {/* BARRA SUPERIOR: BRANDING & CONTROLES */}
            <header className="relative z-10 flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div className="flex items-center gap-3">
                    <Link
                        href="/asistencias"
                        className="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900/80 text-slate-400 hover:text-white transition"
                        title="Volver al panel"
                    >
                        <ArrowLeft className="h-5 w-5" />
                    </Link>
                    <div className="flex items-center gap-2.5">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-lime-400 to-emerald-500 shadow-md">
                            <Dumbbell className="h-5 w-5 text-slate-950" />
                        </div>
                        <div>
                            <span className="text-lg font-black tracking-tight text-white uppercase italic">
                                FITLIFE <span className="text-lime-400 not-italic">KIOSK</span>
                            </span>
                            <span className="block text-[10px] font-bold text-slate-500 tracking-wider">
                                TERMINAL DE AUTOSERVICIO EN RECEPCIÓN
                            </span>
                        </div>
                    </div>
                </div>

                {/* Selector Modo: Check-In vs Check-Out */}
                <div className="flex items-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900/90 p-1">
                    <button
                        type="button"
                        onClick={() => {
                            setModo('entrada');
                            limpiarPantalla();
                        }}
                        className={`flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition cursor-pointer ${
                            modo === 'entrada'
                                ? 'bg-lime-500 text-slate-950 shadow-md shadow-lime-500/20'
                                : 'text-slate-400 hover:text-white'
                        }`}
                    >
                        <Zap className="h-4 w-4" />
                        <span>ENTRADA (CHECK-IN)</span>
                    </button>
                    <button
                        type="button"
                        onClick={() => {
                            setModo('salida');
                            limpiarPantalla();
                            setClaseSeleccionada(null);
                        }}
                        className={`flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition cursor-pointer ${
                            modo === 'salida'
                                ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
                                : 'text-slate-400 hover:text-white'
                        }`}
                    >
                        <LogOut className="h-4 w-4" />
                        <span>SALIDA (CHECK-OUT)</span>
                    </button>
                </div>
            </header>

            {/* CONTENIDO PRINCIPAL: TECLADO TÁCTIL O FEEDBACK VISUAL */}
            <main className="relative z-10 my-auto py-6 max-w-4xl mx-auto w-full">
                {resultado ? (
                    /* TARJETA DE RESULTADO DE ACCESO (FULL FEEDBACK) */
                    <div
                        className={`rounded-3xl border p-8 text-center backdrop-blur-2xl transition-all duration-500 animate-in fade-in zoom-in-95 shadow-2xl ${
                            resultado.exito
                                ? 'border-lime-500/50 bg-gradient-to-b from-lime-950/40 via-slate-900/90 to-slate-950 shadow-lime-500/20'
                                : 'border-rose-500/50 bg-gradient-to-b from-rose-950/40 via-slate-900/90 to-slate-950 shadow-rose-500/20'
                        }`}
                    >
                        <div className="flex justify-center mb-4">
                            {resultado.exito ? (
                                <div className="flex h-24 w-24 items-center justify-center rounded-full bg-lime-500/20 text-lime-400 ring-8 ring-lime-500/10 shadow-[0_0_50px_rgba(132,204,22,0.4)] animate-bounce">
                                    <CheckCircle2 className="h-14 w-14" />
                                </div>
                            ) : (
                                <div className="flex h-24 w-24 items-center justify-center rounded-full bg-rose-500/20 text-rose-400 ring-8 ring-rose-500/10 shadow-[0_0_50px_rgba(244,63,94,0.4)]">
                                    <XCircle className="h-14 w-14" />
                                </div>
                            )}
                        </div>

                        <h2 className="text-3xl sm:text-4xl font-black text-white tracking-tight">
                            {resultado.exito
                                ? modo === 'entrada'
                                    ? '¡Acceso Autorizado!'
                                    : '¡Salida Registrada!'
                                : 'Acceso Denegado'}
                        </h2>

                        <p
                            className={`mt-2 text-base sm:text-lg font-medium ${
                                resultado.exito ? 'text-lime-300' : 'text-rose-300'
                            }`}
                        >
                            {resultado.mensaje}
                        </p>

                        {/* Detalles del Socio / Permanencia */}
                        {resultado.socio && (
                            <div className="mt-6 mx-auto max-w-md rounded-2xl border border-slate-800 bg-slate-900/80 p-4 text-left">
                                <div className="flex items-center justify-between pb-3 border-b border-slate-800">
                                    <div>
                                        <div className="text-sm font-bold text-white">
                                            {resultado.socio.nombre_completo}
                                        </div>
                                        <div className="text-xs text-slate-400">
                                            {resultado.socio.codigo ? `Código: ${resultado.socio.codigo}` : ''}
                                        </div>
                                    </div>
                                    {resultado.socio.plan && (
                                        <span className="rounded-md bg-lime-500/10 border border-lime-500/20 px-2.5 py-1 text-xs font-extrabold text-lime-400 uppercase">
                                            {resultado.socio.plan}
                                        </span>
                                    )}
                                </div>

                                <div className="grid grid-cols-2 gap-3 pt-3 text-xs">
                                    {modo === 'entrada' ? (
                                        <>
                                            <div>
                                                <span className="text-slate-500 block">Hora de Entrada:</span>
                                                <span className="font-mono font-bold text-slate-200">
                                                    {resultado.hora_entrada || new Date().toLocaleTimeString()}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-slate-500 block">Vigencia Plan:</span>
                                                <span className="font-mono font-bold text-slate-200">
                                                    {resultado.socio.es_por_sesiones
                                                        ? `${resultado.socio.sesiones_restantes} sesiones`
                                                        : `Vence: ${resultado.socio.vence}`}
                                                </span>
                                            </div>
                                        </>
                                    ) : (
                                        <>
                                            <div>
                                                <span className="text-slate-500 block">Hora de Salida:</span>
                                                <span className="font-mono font-bold text-slate-200">
                                                    {resultado.hora_salida || new Date().toLocaleTimeString()}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-slate-500 block">Tiempo en Gimnasio:</span>
                                                <span className="font-mono font-bold text-amber-400">
                                                    {resultado.tiempo_permanencia_minutos} minutos
                                                </span>
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        )}

                        <div className="mt-8 flex items-center justify-center gap-4">
                            <button
                                type="button"
                                onClick={limpiarPantalla}
                                className="rounded-xl bg-slate-800 hover:bg-slate-700 px-6 py-2.5 text-xs font-bold text-white transition cursor-pointer"
                            >
                                Marcar Otro Socio ({conteoRegresivo}s)
                            </button>
                        </div>
                    </div>
                ) : (
                    /* FORMULARIO Y TECLADO NUMÉRICO TÁCTIL */
                    <div className="grid md:grid-cols-12 gap-8 items-start">
                        {/* Columna Izquierda: Input y Clases Opcionales */}
                        <div className="md:col-span-6 space-y-6">
                            <div>
                                <span className="text-xs font-bold tracking-widest uppercase text-lime-400 flex items-center gap-1.5 mb-2">
                                    <Sparkles className="h-3.5 w-3.5" />
                                    {modo === 'entrada' ? 'Marcar Entrada' : 'Marcar Salida'}
                                </span>
                                <h1 className="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                    Digita tu Cédula o Código
                                </h1>
                                <p className="text-xs text-slate-400 mt-1">
                                    Introduce tu número de documento de identidad o código de socio en la pantalla.
                                </p>
                            </div>

                            {/* Pantalla del Identificador */}
                            <div className="relative">
                                <input
                                    type="text"
                                    readOnly
                                    value={identificador}
                                    placeholder="Ej: 12345678"
                                    className="w-full text-center tracking-widest text-2xl sm:text-3xl font-mono font-bold rounded-2xl border border-slate-700 bg-slate-900/90 py-4 px-4 text-white placeholder-slate-600 focus:outline-hidden ring-2 ring-lime-500/20"
                                />
                                {identificador && (
                                    <button
                                        type="button"
                                        onClick={limpiarPantalla}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-rose-400 p-2 text-xs font-bold uppercase transition"
                                    >
                                        Limpiar
                                    </button>
                                )}
                            </div>

                            {/* Selección de Clase Grupal (Sólo en modo Entrada) */}
                            {modo === 'entrada' && clases.length > 0 && (
                                <div className="rounded-2xl border border-slate-800 bg-slate-900/50 p-4">
                                    <label className="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2">
                                        ¿Asistes a una clase grupal hoy?
                                    </label>
                                    <div className="grid grid-cols-2 gap-2">
                                        <button
                                            type="button"
                                            onClick={() => setClaseSeleccionada(null)}
                                            className={`rounded-xl border p-2 text-left text-xs transition cursor-pointer ${
                                                claseSeleccionada === null
                                                    ? 'border-lime-500 bg-lime-500/10 text-white font-bold'
                                                    : 'border-slate-800 bg-slate-950/60 text-slate-400 hover:border-slate-700'
                                            }`}
                                        >
                                            <span className="block font-bold">General</span>
                                            <span className="text-[10px] text-slate-500">Musculación / Cardio</span>
                                        </button>

                                        {clases.map((clase) => (
                                            <button
                                                key={clase.id}
                                                type="button"
                                                onClick={() => setClaseSeleccionada(clase.id)}
                                                className={`rounded-xl border p-2 text-left text-xs transition cursor-pointer ${
                                                    claseSeleccionada === clase.id
                                                        ? 'border-cyan-400 bg-cyan-400/10 text-white font-bold'
                                                        : 'border-slate-800 bg-slate-950/60 text-slate-400 hover:border-slate-700'
                                                }`}
                                            >
                                                <span className="block font-bold truncate">{clase.nombre}</span>
                                                <span className="text-[10px] text-cyan-400">
                                                    Cupos: {clase.cupos_disponibles}/{clase.cupo_maximo}
                                                </span>
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Botón de Confirmación Grande */}
                            <button
                                type="button"
                                disabled={!identificador || procesando}
                                onClick={() => procesarMarcaje()}
                                className={`w-full flex items-center justify-center gap-2 rounded-2xl py-4 text-base font-black uppercase tracking-wider transition-all duration-300 cursor-pointer shadow-lg ${
                                    modo === 'entrada'
                                        ? 'bg-gradient-to-r from-lime-400 to-emerald-500 text-slate-950 hover:from-lime-300 hover:to-emerald-400 shadow-lime-500/30'
                                        : 'bg-gradient-to-r from-amber-400 to-orange-500 text-slate-950 hover:from-amber-300 hover:to-orange-400 shadow-amber-500/30'
                                } disabled:opacity-40 disabled:cursor-not-allowed`}
                            >
                                {procesando ? (
                                    <>
                                        <LoaderCircle className="h-5 w-5 animate-spin" />
                                        <span>Validando Acceso...</span>
                                    </>
                                ) : (
                                    <>
                                        <CheckCircle2 className="h-5 w-5" />
                                        <span>
                                            {modo === 'entrada' ? 'Confirmar Entrada' : 'Confirmar Salida'}
                                        </span>
                                    </>
                                )}
                            </button>
                        </div>

                        {/* Columna Derecha: Teclado Numérico Touchscreen */}
                        <div className="md:col-span-6 rounded-3xl border border-slate-800 bg-slate-900/60 p-4 sm:p-6 backdrop-blur-xl shadow-2xl">
                            <div className="grid grid-cols-3 gap-3">
                                {['1', '2', '3', '4', '5', '6', '7', '8', '9'].map((num) => (
                                    <button
                                        key={num}
                                        type="button"
                                        onClick={() => presionarDigito(num)}
                                        className="h-16 sm:h-20 rounded-2xl border border-slate-800 bg-slate-950/80 text-2xl sm:text-3xl font-mono font-bold text-white hover:bg-slate-800 hover:border-lime-500/50 active:scale-95 transition shadow-sm cursor-pointer flex items-center justify-center"
                                    >
                                        {num}
                                    </button>
                                ))}

                                <button
                                    type="button"
                                    onClick={borrarDigito}
                                    className="h-16 sm:h-20 rounded-2xl border border-slate-800 bg-slate-950/80 text-rose-400 hover:bg-rose-950/30 hover:border-rose-500/50 active:scale-95 transition flex items-center justify-center cursor-pointer"
                                    title="Borrar dígito"
                                >
                                    <Delete className="h-7 w-7" />
                                </button>

                                <button
                                    type="button"
                                    onClick={() => presionarDigito('0')}
                                    className="h-16 sm:h-20 rounded-2xl border border-slate-800 bg-slate-950/80 text-2xl sm:text-3xl font-mono font-bold text-white hover:bg-slate-800 hover:border-lime-500/50 active:scale-95 transition shadow-sm cursor-pointer flex items-center justify-center"
                                >
                                    0
                                </button>

                                <button
                                    type="button"
                                    onClick={() => procesarMarcaje()}
                                    disabled={!identificador || procesando}
                                    className="h-16 sm:h-20 rounded-2xl bg-lime-500 hover:bg-lime-400 active:scale-95 text-slate-950 font-black text-sm tracking-wider uppercase transition shadow-md shadow-lime-500/30 flex items-center justify-center disabled:opacity-40 cursor-pointer"
                                >
                                    {procesando ? (
                                        <LoaderCircle className="h-6 w-6 animate-spin" />
                                    ) : (
                                        'OK'
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </main>

            {/* PIE DE PÁGINA: ESTADO DEL SISTEMA */}
            <footer className="relative z-10 flex items-center justify-between text-xs text-slate-500 border-t border-slate-800/80 pt-4">
                <div className="flex items-center gap-2">
                    <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Torniquete & Lector QR Online • Sede Principal</span>
                </div>
                <span>FitLife OS v2.4 Kiosk Mode</span>
            </footer>
        </div>
    );
}


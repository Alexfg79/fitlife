import { CheckCircle2, Dumbbell, QrCode, Sparkles, XCircle } from 'lucide-react';
import React, { useState } from 'react';

interface CredencialSocioProps {
    socio: {
        id?: number;
        nombre: string;
        apellido: string;
        codigo: string;
        dni: string;
        foto?: string;
        estado?: string;
        plan?: string;
        vence?: string;
        dias_restantes?: number;
    };
    esActiva?: boolean;
    onRenovar?: () => void;
}

export default function CredencialDigitalSocio({
    socio,
    esActiva = true,
    onRenovar,
}: CredencialSocioProps) {
    const [rotX, setRotX] = useState(0);
    const [rotY, setRotY] = useState(0);
    const [brillo, setBrillo] = useState(50);
    const [esHover, setEsHover] = useState(false);

    const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
        const rect = e.currentTarget.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        // Inclinación reactiva en grados
        setRotX(((y - centerY) / centerY) * -12);
        setRotY(((x - centerX) / centerX) * 12);
        setBrillo((x / rect.width) * 100);
    };

    const handleMouseLeave = () => {
        setRotX(0);
        setRotY(0);
        setEsHover(false);
    };

    const avatarUrl =
        socio.foto ||
        `https://ui-avatars.com/api/?name=${encodeURIComponent(
            `${socio.nombre}+${socio.apellido}`
        )}&background=1e293b&color=84cc16&bold=true`;

    return (
        <div
            onMouseMove={handleMouseMove}
            onMouseEnter={() => setEsHover(true)}
            onMouseLeave={handleMouseLeave}
            className="w-full max-w-sm cursor-pointer select-none perspective-[1000px]"
        >
            <div
                style={{
                    transform: `perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg)`,
                    transition: esHover ? 'transform 100ms ease-out' : 'transform 400ms ease-out',
                }}
                className={`glass-card relative overflow-hidden rounded-3xl p-6 transition-all duration-300 ${
                    esActiva ? 'glass-glow-lime' : 'glass-glow-rose'
                }`}
            >
                {/* Reflejo dinámico holográfico tipo tarjeta de crédito */}
                <div
                    className="pointer-events-none absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent transition-opacity duration-300"
                    style={{
                        opacity: esHover ? 0.8 : 0,
                        backgroundPosition: `${brillo}%`,
                    }}
                />

                {/* Cabecera: Logo de FitLife Pass y Chip Dorado de Acceso */}
                <div className="relative z-10 flex items-center justify-between border-b border-slate-700/60 pb-3.5">
                    <div className="flex items-center gap-2.5">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-lime-400 text-slate-950 font-black shadow-[0_0_15px_rgba(132,204,22,0.6)]">
                            <Dumbbell className="h-5 w-5 text-slate-950" />
                        </div>
                        <div>
                            <h3 className="text-sm font-black tracking-tight text-white uppercase italic">
                                FITLIFE <span className="text-lime-400 not-italic">PASS</span>
                            </h3>
                            <span className="font-mono text-[9px] font-bold text-slate-400 tracking-wider">
                                CREDENCIAL DIGITAL
                            </span>
                        </div>
                    </div>

                    {/* Chip Holográfico de Acceso */}
                    <div className="h-7 w-10 rounded-lg border border-amber-400/50 bg-gradient-to-tr from-amber-500/20 via-amber-300/30 to-amber-600/20 p-1 flex items-center justify-center shadow-inner">
                        <div className="h-full w-full rounded-xs border border-amber-400/60 bg-amber-400/10"></div>
                    </div>
                </div>

                {/* Cuerpo Central: Foto, Nombre e Indicador Neón */}
                <div className="relative z-10 my-4 flex items-center gap-4">
                    <div className="relative shrink-0">
                        <img
                            src={avatarUrl}
                            alt={`${socio.nombre} ${socio.apellido}`}
                            className={`h-16 w-16 rounded-2xl object-cover ring-2 shadow-lg ${
                                esActiva ? 'ring-lime-400 shadow-lime-500/30' : 'ring-rose-500 shadow-rose-500/30'
                            }`}
                        />
                        <span className="absolute -bottom-1 -right-1 flex h-4 w-4">
                            <span
                                className={`absolute inline-flex h-full w-full animate-ping rounded-full opacity-75 ${
                                    esActiva ? 'bg-lime-400' : 'bg-rose-500'
                                }`}
                            />
                            <span
                                className={`relative inline-flex h-4 w-4 rounded-full border-2 border-slate-900 ${
                                    esActiva ? 'bg-lime-400' : 'bg-rose-500'
                                }`}
                            />
                        </span>
                    </div>

                    <div className="overflow-hidden">
                        <h4 className="text-base font-bold text-white truncate leading-tight">
                            {socio.nombre} {socio.apellido}
                        </h4>
                        <p className="font-mono text-xs font-bold text-lime-400 mt-0.5">
                            {socio.codigo}
                        </p>
                        <p className="text-[11px] text-slate-400 truncate">
                            DNI: {socio.dni}
                        </p>
                        {socio.plan && (
                            <span className="inline-block mt-1 rounded-md bg-slate-800/80 px-2 py-0.5 text-[10px] font-bold text-slate-300">
                                {socio.plan}
                            </span>
                        )}
                    </div>
                </div>

                {/* Pie de la Credencial: Estado de Suscripción + QR Escaneable */}
                <div className="relative z-10 flex items-center justify-between border-t border-slate-800/80 pt-3">
                    <div>
                        <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                            Membresía
                        </span>
                        {esActiva ? (
                            <span className="inline-flex items-center gap-1.5 rounded-full border border-lime-400/40 bg-lime-400/10 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-lime-400 shadow-[0_0_15px_rgba(132,204,22,0.3)]">
                                <span className="h-2 w-2 rounded-full bg-lime-400 animate-pulse" />
                                ACTIVA
                            </span>
                        ) : (
                            <div className="flex items-center gap-2">
                                <span className="inline-flex items-center gap-1 rounded-full border border-rose-500/40 bg-rose-500/10 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-rose-400 shadow-[0_0_15px_rgba(244,63,94,0.3)]">
                                    <XCircle className="h-3.5 w-3.5" />
                                    VENCIDA
                                </span>
                                {onRenovar && (
                                    <button
                                        type="button"
                                        onClick={onRenovar}
                                        className="rounded-lg bg-rose-500 px-2 py-1 text-[10px] font-bold text-white hover:bg-rose-600 transition"
                                    >
                                        Renovar
                                    </button>
                                )}
                            </div>
                        )}
                    </div>

                    <div className="rounded-xl border border-slate-700/80 bg-slate-950 p-2 shadow-inner group-hover:border-lime-400/60 transition">
                        <QrCode className="h-9 w-9 text-slate-200" />
                    </div>
                </div>
            </div>
        </div>
    );
}


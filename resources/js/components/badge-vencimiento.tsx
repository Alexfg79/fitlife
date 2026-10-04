import { AlertTriangle, Clock } from 'lucide-react';
import React from 'react';

interface BadgeVencimientoProps {
    diasRestantes: number;
    fechaFin: string;
    estaVencida?: boolean;
    className?: string;
}

export default function BadgeVencimiento({
    diasRestantes,
    fechaFin,
    estaVencida = false,
    className = '',
}: BadgeVencimientoProps) {
    if (estaVencida) {
        return (
            <div
                className={`inline-flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-600 dark:text-rose-400 backdrop-blur-md shadow-[0_0_12px_rgba(244,63,94,0.15)] ${className}`}
            >
                <span className="relative flex h-2 w-2">
                    <span className="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                </span>
                <span>Vencida ({fechaFin})</span>
            </div>
        );
    }

    // Alerta de vencimiento inminente (menos o igual a 3 días)
    const esInminente = diasRestantes <= 3;

    return (
        <div
            className={`inline-flex items-center gap-2 rounded-xl border px-3 py-1 text-xs font-bold backdrop-blur-md transition-all ${
                esInminente
                    ? 'border-amber-500/40 bg-amber-500/15 text-amber-700 dark:text-amber-300 shadow-[0_0_15px_rgba(245,158,11,0.2)]'
                    : 'border-slate-200 dark:border-slate-800 bg-slate-100/90 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300'
            } ${className}`}
        >
            {/* Halo expansivo / Ping animado para casos inminentes */}
            {esInminente && (
                <span className="relative flex h-2.5 w-2.5">
                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-85"></span>
                    <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.8)]"></span>
                </span>
            )}

            <div className="flex items-center gap-1.5">
                <span>
                    {diasRestantes === 0 ? 'Vence Hoy' : `Expira en ${diasRestantes} día${diasRestantes > 1 ? 's' : ''}`}
                </span>
                <span className="rounded bg-amber-400/20 px-1.5 py-0.5 font-mono text-[10px] font-bold text-amber-700 dark:text-amber-300">
                    {fechaFin}
                </span>
            </div>
        </div>
    );
}


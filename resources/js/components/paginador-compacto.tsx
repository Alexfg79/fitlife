import { router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import React from 'react';

export interface PaginatorMeta {
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    from?: number | null;
    to?: number | null;
    prev_page_url?: string | null;
    next_page_url?: string | null;
}

interface PaginadorCompactoProps {
    paginacion: PaginatorMeta;
    className?: string;
    variant?: 'default' | 'dark';
}

export default function PaginadorCompacto({
    paginacion,
    className = '',
    variant = 'default',
}: PaginadorCompactoProps) {
    const { current_page, last_page, total, from, to, prev_page_url, next_page_url } = paginacion;

    if (!total || total === 0) {
        return null;
    }

    const puedeAnterior = Boolean(prev_page_url) && current_page > 1;
    const puedeSiguiente = Boolean(next_page_url) && current_page < last_page;

    const irA = (url?: string | null) => {
        if (!url) return;
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    };

    const isDark = variant === 'dark';

    return (
        <div className={`flex items-center justify-end gap-3 select-none text-xs sm:text-sm font-sans ${className}`}>
            <span
                className={`font-mono text-xs sm:text-sm tracking-tight ${
                    isDark ? 'text-slate-400' : 'text-neutral-500 dark:text-neutral-400'
                }`}
            >
                {from ?? 0}–{to ?? 0} de {total.toLocaleString('es-ES')}
            </span>

            <div className="flex items-center gap-1">
                <button
                    type="button"
                    disabled={!puedeAnterior}
                    onClick={() => irA(prev_page_url)}
                    className={`flex h-8 w-8 items-center justify-center rounded-lg transition cursor-pointer disabled:cursor-not-allowed ${
                        puedeAnterior
                            ? isDark
                                ? 'text-slate-300 hover:bg-slate-800 hover:text-white active:scale-95'
                                : 'text-neutral-700 hover:bg-neutral-100 dark:text-neutral-200 dark:hover:bg-neutral-800 active:scale-95'
                            : isDark
                              ? 'text-slate-700 opacity-40 cursor-not-allowed'
                              : 'text-neutral-300 dark:text-neutral-700 opacity-40 cursor-not-allowed'
                    }`}
                    title="Página anterior"
                    aria-label="Página anterior"
                >
                    <ChevronLeft className="h-4 w-4" />
                </button>

                <button
                    type="button"
                    disabled={!puedeSiguiente}
                    onClick={() => irA(next_page_url)}
                    className={`flex h-8 w-8 items-center justify-center rounded-lg transition cursor-pointer disabled:cursor-not-allowed ${
                        puedeSiguiente
                            ? isDark
                                ? 'text-slate-300 hover:bg-slate-800 hover:text-white active:scale-95'
                                : 'text-neutral-700 hover:bg-neutral-100 dark:text-neutral-200 dark:hover:bg-neutral-800 active:scale-95'
                            : isDark
                              ? 'text-slate-700 opacity-40 cursor-not-allowed'
                              : 'text-neutral-300 dark:text-neutral-700 opacity-40 cursor-not-allowed'
                    }`}
                    title="Página siguiente"
                    aria-label="Página siguiente"
                >
                    <ChevronRight className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}


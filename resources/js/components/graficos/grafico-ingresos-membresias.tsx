import { CreditCard, DollarSign, PieChart, Sparkles } from 'lucide-react';
import React, { useEffect, useRef } from 'react';

declare global {
    interface Window {
        Chart: any;
    }
}

export interface IngresoPlanItem {
    categoria: string;
    total: number;
    cantidad: number;
    color?: string;
}

interface Props {
    datos?: IngresoPlanItem[];
}

export default function GraficoIngresosMembresias({ datos }: Props) {
    const canvasRef = useRef<HTMLCanvasElement | null>(null);
    const chartInstanceRef = useRef<any>(null);

    const items: IngresoPlanItem[] = datos && datos.length > 0 ? datos : [
        {
            categoria: 'Plan Mensual',
            total: 4250.00,
            cantidad: 115,
            color: '#84cc16', // Verde Lima
        },
        {
            categoria: 'Plan Anual',
            total: 7800.00,
            cantidad: 18,
            color: '#06b6d4', // Cian Neón
        },
        {
            categoria: 'Pase Libre',
            total: 3600.00,
            cantidad: 64,
            color: '#a855f7', // Violeta Eléctrico
        },
    ];

    const totalIngresos = items.reduce((acc, curr) => acc + Number(curr.total), 0);
    const totalSuscripciones = items.reduce((acc, curr) => acc + Number(curr.cantidad), 0);

    const formatMonto = (valor: number) => {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0,
        }).format(valor);
    };

    useEffect(() => {
        let isMounted = true;

        const initChart = () => {
            if (!canvasRef.current || !window.Chart || !isMounted) return;

            const ctx = canvasRef.current.getContext('2d');
            if (!ctx) return;

            if (chartInstanceRef.current) {
                chartInstanceRef.current.destroy();
            }

            const esOscuro = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
            const labels = items.map((i) => i.categoria);
            const dataValores = items.map((i) => Number(i.total));
            const colores = items.map((i) => i.color || '#84cc16');

            chartInstanceRef.current = new window.Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: dataValores,
                            backgroundColor: colores,
                            borderColor: esOscuro ? '#090d16' : '#ffffff',
                            borderWidth: 4,
                            hoverOffset: 8,
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: esOscuro ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.98)',
                            titleColor: esOscuro ? '#ffffff' : '#0f172a',
                            bodyColor: esOscuro ? '#e2e8f0' : '#334155',
                            borderColor: esOscuro ? 'rgba(51, 65, 85, 0.5)' : 'rgba(203, 213, 225, 0.8)',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            cornerRadius: 10,
                            callbacks: {
                                label: (context: any) => {
                                    const val = context.parsed;
                                    const pct = totalIngresos > 0 ? ((val / totalIngresos) * 100).toFixed(1) : '0';
                                    return ` ${formatMonto(val)} (${pct}%)`;
                                },
                            },
                        },
                    },
                },
            });
        };

        if (!window.Chart) {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js';
            script.async = true;
            script.onload = () => {
                if (isMounted) initChart();
            };
            document.head.appendChild(script);
        } else {
            initChart();
        }

        return () => {
            isMounted = false;
            if (chartInstanceRef.current) {
                chartInstanceRef.current.destroy();
            }
        };
    }, [items, totalIngresos]);

    return (
        <div className="rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/90 dark:to-slate-950 p-5 md:p-6 shadow-xl dark:shadow-2xl backdrop-blur-md flex flex-col justify-between">
            {/* Header */}
            <div className="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-4">
                <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-600 dark:text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.25)]">
                        <PieChart className="h-5 w-5" />
                    </div>
                    <div>
                        <h2 className="text-base font-bold text-slate-950 dark:text-white uppercase tracking-wide">
                            Ingresos por Membresía
                        </h2>
                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                            Distribución de facturación según categoría de plan
                        </p>
                    </div>
                </div>

                <span className="flex items-center gap-1 rounded-full border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-[10px] font-mono font-bold text-slate-700 dark:text-slate-300">
                    <Sparkles className="h-3 w-3 text-lime-600 dark:text-lime-400" />
                    {totalSuscripciones} Activas
                </span>
            </div>

            {/* Contenedor Gráfico Donut + Centro Métrico */}
            <div className="my-5 grid grid-cols-1 sm:grid-cols-12 items-center gap-6">
                {/* Visualizador Donut con Centro Absoluto */}
                <div className="sm:col-span-6 relative flex items-center justify-center">
                    <div className="relative h-56 w-56 sm:h-60 sm:w-60">
                        <canvas ref={canvasRef} />

                        {/* Métrica Central Flotante */}
                        <div className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Facturado
                            </span>
                            <span className="font-mono text-xl sm:text-2xl font-black text-slate-950 dark:text-white">
                                {formatMonto(totalIngresos)}
                            </span>
                            <span className="text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                                100% Recaudado
                            </span>
                        </div>
                    </div>
                </div>

                {/* Leyenda Personalizada con Tailwind CSS */}
                <div className="sm:col-span-6 space-y-3">
                    {items.map((item, idx) => {
                        const porcentaje = totalIngresos > 0 
                            ? ((item.total / totalIngresos) * 100).toFixed(1) 
                            : '0';

                        return (
                            <div
                                key={idx}
                                className="group relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800/80 bg-slate-50/80 dark:bg-slate-950/60 p-3 transition hover:border-slate-300 dark:hover:border-slate-700 hover:bg-slate-100/80 dark:hover:bg-slate-900/60"
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2.5">
                                        <span
                                            className="h-3 w-3 rounded-full shrink-0 shadow-xs"
                                            style={{ backgroundColor: item.color }}
                                        />
                                        <div>
                                            <span className="text-xs font-bold text-slate-950 dark:text-white block group-hover:text-lime-600 dark:group-hover:text-lime-300 transition">
                                                {item.categoria}
                                            </span>
                                            <span className="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                                {item.cantidad} suscripciones
                                            </span>
                                        </div>
                                    </div>

                                    <div className="text-right">
                                        <span className="font-mono text-xs font-black text-slate-950 dark:text-white block">
                                            {formatMonto(item.total)}
                                        </span>
                                        <span
                                            className="text-[10px] font-bold font-mono"
                                            style={{ color: item.color }}
                                        >
                                            {porcentaje}%
                                        </span>
                                    </div>
                                </div>

                                {/* Barra de porcentaje en miniatura */}
                                <div className="mt-2 h-1 w-full rounded-full bg-slate-200 dark:bg-slate-800/80 overflow-hidden">
                                    <div
                                        className="h-full rounded-full transition-all duration-500"
                                        style={{
                                            width: `${porcentaje}%`,
                                            backgroundColor: item.color,
                                        }}
                                    />
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Footer con resumen rápido */}
            <div className="border-t border-slate-200 dark:border-slate-800/60 pt-3 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span className="flex items-center gap-1">
                    <DollarSign className="h-3.5 w-3.5 text-lime-600 dark:text-lime-400" />
                    Comparativa de facturación consolidada
                </span>
                <span className="font-mono text-slate-400 dark:text-slate-500">FitLife Analytics</span>
            </div>
        </div>
    );
}


import { Activity, Clock, Flame, TrendingUp } from 'lucide-react';
import React, { useEffect, useRef } from 'react';

declare global {
    interface Window {
        Chart: any;
    }
}

interface ConcurrenciaData {
    etiquetas: string[];
    valores: number[];
}

interface Props {
    datos?: ConcurrenciaData;
}

export default function GraficoConcurrencia({ datos }: Props) {
    const canvasRef = useRef<HTMLCanvasElement | null>(null);
    const chartInstanceRef = useRef<any>(null);

    const etiquetas = datos?.etiquetas && datos.etiquetas.length > 0 
        ? datos.etiquetas 
        : ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'];

    const valores = datos?.valores && datos.valores.length > 0 
        ? datos.valores 
        : [8, 19, 28, 22, 14, 11, 16, 21, 15, 12, 18, 31, 46, 52, 38, 20, 7];

    const maxConcurrencia = Math.max(...valores, 1);
    const horaPicoIndex = valores.indexOf(maxConcurrencia);
    const horaPico = etiquetas[horaPicoIndex] || '19:00';
    const totalHoy = valores.reduce((acc, curr) => acc + curr, 0);

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

            // Gradiente Neón Verde Lima para el área suave
            const gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, 'rgba(132, 204, 22, 0.45)');
            gradient.addColorStop(0.5, 'rgba(132, 204, 22, 0.18)');
            gradient.addColorStop(1, 'rgba(132, 204, 22, 0.00)');

            chartInstanceRef.current = new window.Chart(ctx, {
                type: 'line',
                data: {
                    labels: etiquetas,
                    datasets: [
                        {
                            label: 'Asistencias',
                            data: valores,
                            borderColor: '#84cc16',
                            borderWidth: 2.5,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.42,
                            pointBackgroundColor: '#84cc16',
                            pointBorderColor: esOscuro ? '#090d16' : '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 3.5,
                            pointHoverRadius: 7,
                            pointHoverBackgroundColor: '#ffffff',
                            pointHoverBorderColor: '#84cc16',
                            pointHoverBorderWidth: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: esOscuro ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.98)',
                            titleColor: esOscuro ? '#f8fafc' : '#0f172a',
                            bodyColor: esOscuro ? '#a3e635' : '#65a30d',
                            borderColor: esOscuro ? 'rgba(132, 204, 22, 0.3)' : 'rgba(132, 204, 22, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            cornerRadius: 10,
                            displayColors: false,
                            callbacks: {
                                title: (items: any) => `Horario: ${items[0].label} hrs`,
                                label: (context: any) => ` ${context.parsed.y} socios ingresaron`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: esOscuro ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.05)',
                                tickColor: 'transparent',
                            },
                            ticks: {
                                color: esOscuro ? 'rgba(148, 163, 184, 0.7)' : 'rgba(100, 116, 139, 0.9)',
                                font: {
                                    size: 11,
                                    family: 'ui-monospace, SFMono-Regular, Menlo, monospace',
                                },
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 9,
                            },
                            border: {
                                display: false,
                            },
                        },
                        y: {
                            min: 0,
                            suggestedMax: Math.ceil(maxConcurrencia * 1.15),
                            grid: {
                                color: esOscuro ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.06)',
                                tickColor: 'transparent',
                            },
                            ticks: {
                                color: esOscuro ? 'rgba(148, 163, 184, 0.7)' : 'rgba(100, 116, 139, 0.9)',
                                font: {
                                    size: 11,
                                    family: 'ui-monospace, SFMono-Regular, Menlo, monospace',
                                },
                                stepSize: Math.max(5, Math.ceil(maxConcurrencia / 4)),
                            },
                            border: {
                                display: false,
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
    }, [datos]);

    return (
        <div className="rounded-2xl border border-slate-200 dark:border-slate-800/90 bg-white dark:bg-gradient-to-b dark:from-slate-900/90 dark:to-slate-950 p-5 md:p-6 shadow-xl dark:shadow-2xl backdrop-blur-md">
            {/* Header del Gráfico */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-500/10 border border-lime-500/30 text-lime-600 dark:text-lime-400 shadow-[0_0_15px_rgba(132,204,22,0.25)]">
                        <Activity className="h-5 w-5" />
                    </div>
                    <div>
                        <div className="flex items-center gap-2">
                            <h2 className="text-base font-bold text-slate-950 dark:text-white uppercase tracking-wide">
                                Concurrencia de Hoy
                            </h2>
                            <span className="flex items-center gap-1 rounded-full bg-lime-500/15 border border-lime-500/30 px-2 py-0.5 text-[10px] font-bold text-lime-700 dark:text-lime-400">
                                <span className="h-1.5 w-1.5 rounded-full bg-lime-500 animate-pulse"></span>
                                EN VIVO
                            </span>
                        </div>
                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                            Flujo de socios hora por hora en las instalaciones
                        </p>
                    </div>
                </div>

                {/* Métricas rápidas */}
                <div className="flex items-center gap-2 sm:gap-3">
                    <div className="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 px-3 py-1.5 text-right">
                        <span className="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                            Pico Máximo
                        </span>
                        <span className="font-mono text-xs font-black text-lime-700 dark:text-lime-400 flex items-center justify-end gap-1">
                            <Flame className="h-3 w-3" /> {maxConcurrencia} ({horaPico})
                        </span>
                    </div>

                    <div className="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 px-3 py-1.5 text-right">
                        <span className="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                            Total Registros
                        </span>
                        <span className="font-mono text-xs font-black text-slate-950 dark:text-white flex items-center justify-end gap-1">
                            <Clock className="h-3 w-3 text-cyan-600 dark:text-cyan-400" /> {totalHoy}
                        </span>
                    </div>
                </div>
            </div>

            {/* Contenedor del Canvas Adaptable */}
            <div className="relative mt-5 h-64 md:h-72 w-full">
                <canvas ref={canvasRef} />
            </div>

            {/* Footer con micro-insights */}
            <div className="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 dark:border-slate-800/60 pt-3 text-[11px] text-slate-500 dark:text-slate-400">
                <div className="flex items-center gap-2">
                    <span className="flex h-2 w-2 rounded-full bg-lime-500"></span>
                    <span>Zona horaria gimnasio: 06:00 - 22:00</span>
                </div>
                <div className="flex items-center gap-1 text-slate-500 font-mono">
                    <TrendingUp className="h-3.5 w-3.5 text-lime-600 dark:text-lime-400" />
                    <span>Mayor afluencia vespertina (18:00 - 20:00)</span>
                </div>
            </div>
        </div>
    );
}


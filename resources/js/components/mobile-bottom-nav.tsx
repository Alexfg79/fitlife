import { Link, usePage } from '@inertiajs/react';
import { Dumbbell, LayoutGrid, QrCode, User, Users } from 'lucide-react';
import React from 'react';

export default function MobileBottomNav() {
    const { url } = usePage();

    const navItems = [
        {
            name: 'Inicio',
            href: '/dashboard',
            icon: LayoutGrid,
            active: url === '/dashboard' || url === '/',
        },
        {
            name: 'Socios',
            href: '/socios',
            icon: Users,
            active: url.startsWith('/socios'),
        },
        {
            name: 'Acceso QR',
            href: '/asistencias',
            icon: QrCode,
            active: url.startsWith('/asistencias'),
            highlight: true, // Botón central destacado para escaneo rápido
        },
        {
            name: 'Rutinas',
            href: '/rutinas',
            icon: Dumbbell,
            active: url.startsWith('/rutinas'),
        },
        {
            name: 'Perfil',
            href: '/settings/profile',
            icon: User,
            active: url.startsWith('/settings'),
        },
    ];

    return (
        <nav
            aria-label="Navegación móvil"
            className="fixed bottom-0 inset-x-0 z-40 block lg:hidden border-t border-slate-800/80 bg-slate-950/95 backdrop-blur-xl pb-safe shadow-[0_-10px_25px_rgba(0,0,0,0.5)]"
        >
            <div className="grid h-16 grid-cols-5 items-center px-2 max-w-lg mx-auto">
                {navItems.map((item) => {
                    const Icon = item.icon;

                    if (item.highlight) {
                        return (
                            <Link
                                key={item.name}
                                href={item.href}
                                className="relative -top-3 flex flex-col items-center justify-center focus:outline-hidden"
                            >
                                <div
                                    className={`flex h-12 w-12 items-center justify-center rounded-2xl shadow-lg transition-transform active:scale-95 ${
                                        item.active
                                            ? 'bg-lime-400 text-slate-950 shadow-[0_0_20px_rgba(132,204,22,0.5)] ring-4 ring-slate-950'
                                            : 'bg-gradient-to-tr from-lime-500 to-lime-400 text-slate-950 shadow-[0_0_15px_rgba(132,204,22,0.3)] ring-4 ring-slate-950 hover:brightness-110'
                                    }`}
                                >
                                    <Icon className="h-6 w-6 stroke-[2.5]" />
                                </div>
                                <span className="mt-1 text-[10px] font-bold text-lime-400">
                                    {item.name}
                                </span>
                            </Link>
                        );
                    }

                    return (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={`flex flex-col items-center justify-center min-h-[48px] py-1 transition-colors active:scale-95 focus:outline-hidden ${
                                item.active
                                    ? 'text-lime-400 font-bold'
                                    : 'text-slate-400 hover:text-slate-200'
                            }`}
                        >
                            <div className="relative">
                                <Icon className={`h-5 w-5 ${item.active ? 'stroke-[2.5]' : 'stroke-2'}`} />
                                {item.active && (
                                    <span className="absolute -bottom-1 left-1/2 -translate-x-1/2 h-1 w-1 rounded-full bg-lime-400 shadow-[0_0_8px_rgba(132,204,22,1)]" />
                                )}
                            </div>
                            <span className="mt-1 text-[10px] tracking-tight">
                                {item.name}
                            </span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}


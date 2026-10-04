import { Dumbbell } from 'lucide-react';

export default function AppLogo() {
    return (
        <div className="flex items-center gap-2.5">
            <div className="bg-gradient-to-br from-lime-400 to-emerald-500 text-slate-950 flex aspect-square size-8 shrink-0 items-center justify-center rounded-lg shadow-[0_0_12px_rgba(132,204,22,0.35)]">
                <Dumbbell className="size-4.5 text-slate-950 stroke-[2.5]" />
            </div>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-black tracking-tight text-slate-950 dark:text-white">
                    Fit<span className="text-lime-600 dark:text-lime-400">Life</span> <span className="font-bold text-slate-800 dark:text-slate-200">Gym</span>
                </span>
                <span className="truncate text-[10px] font-mono font-bold tracking-wider uppercase text-lime-700 dark:text-lime-400">
                    Gestión OS Pro
                </span>
            </div>
        </div>
    );
}

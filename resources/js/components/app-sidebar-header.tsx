import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { Dumbbell } from 'lucide-react';

export function AppSidebarHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItemType[] }) {
    return (
        <header className="border-sidebar-border/50 sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between border-b bg-background/90 px-4 backdrop-blur-md transition-[width,height] ease-linear md:px-6">
            <div className="flex items-center gap-3">
                {/* Trigger del Menú Hamburguesa con área de toque amplia (Touch UX) */}
                <div className="flex items-center">
                    <SidebarTrigger className="h-10 w-10 p-2 text-slate-300 hover:text-white hover:bg-slate-800/80 rounded-xl transition" />
                </div>
                <div className="hidden sm:block">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
                <div className="sm:hidden flex items-center gap-1.5 font-black tracking-tight text-white text-sm">
                    <div className="h-6 w-6 rounded-lg bg-lime-400 text-slate-950 flex items-center justify-center font-black text-xs">
                        <Dumbbell className="h-3.5 w-3.5 stroke-[2.5]" />
                    </div>
                    <span>FITLIFE</span>
                </div>
            </div>

            <div className="flex items-center gap-2">
                <span className="hidden sm:inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-bold text-emerald-400">
                    <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                    Sede Central
                </span>
            </div>
        </header>
    );
}

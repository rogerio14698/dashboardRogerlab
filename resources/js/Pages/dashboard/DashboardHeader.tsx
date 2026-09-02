import { Link } from '@inertiajs/react';

// Enlaces de navegacion mostrados en la cabecera del dashboard.
const navigationLinks = [
    ['Inicio', '/dashboard'],
    ['Metricas', '/metrics'],
    ['Domains', '/domains'],
    ['Database', '/data-base'],
    ['Containers', '/docker'],
];

type DashboardHeaderProps = {
    onLogout: () => void;
};

// Cabecera reutilizable: identifica la aplicacion, permite navegar y cerrar sesion.
export function DashboardHeader({ onLogout }: DashboardHeaderProps) {
    return (
        <header className="border-b border-white/10 bg-[#141d26]">
            <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-300">Rogerlab |</p>
                    <h1 className="mt-1 text-xl font-semibold text-white">Dashboard del servidor</h1>
                </div>
                <nav className="flex gap-4">
                    {navigationLinks.map(([label, href]) => (
                        <Link key={label} href={href} className="text-sm text-slate-400 hover:text-white">{label}</Link>
                    ))}
                </nav>
                <button onClick={onLogout} className="text-sm text-slate-400 hover:text-white">Cerrar sesion</button>
            </div>
        </header>
    );
}
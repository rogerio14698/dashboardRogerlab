import { Link } from '@inertiajs/react';

// Destinos principales del panel y el texto que describe cada modulo.
const modules = [
    //['Inicio', 'Panel de inicio', '/inicio'],
    ['System metrics', 'CPU, memoria, disco y red', '/system-metrics'],
    ['Docker', 'Contenedores y acciones auditadas', '/docker'],
    ['Uptime', 'Disponibilidad de tus subdominios', '/uptime'],
    ['SEO', 'Salud tecnica por dominio', '/seo'],
    ['n8n', 'Ejecuciones y workflows fallidos', '/n8n'],
];

// Rejilla de accesos rapidos a las secciones de monitorizacion.
export function ModuleCards() {
    return (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {modules.map(([title, description, href]) => (
                <Link key={title} href={href} className="group border border-white/10 bg-[#18232e] p-6 transition hover:-translate-y-1 hover:border-cyan-300/50">
                    <div className="flex items-center justify-between">
                        <h3 className="font-semibold text-white">{title}</h3>
                        <span className="text-cyan-300 transition group-hover:translate-x-1">-&gt;</span>
                    </div>
                    <p className="mt-8 text-sm leading-6 text-slate-400">{description}</p>
                    <div className="mt-6 h-1 w-full bg-slate-700"><div className="h-full w-3/4 bg-emerald-400" /></div>
                </Link>
            ))}
        </div>
    );
}
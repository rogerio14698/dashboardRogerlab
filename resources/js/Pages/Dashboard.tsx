import { Head, Link, useForm } from '@inertiajs/react';

const modules = [
    ['System metrics', 'CPU, memoria, disco y red', '/'],
    ['Docker', 'Contenedores y acciones auditadas', '/docker'],
    ['Uptime', 'Disponibilidad de tus subdominios', '/uptime'],
    ['SEO', 'Salud tecnica por dominio', '/seo'],
    ['n8n', 'Ejecuciones y workflows fallidos', '/n8n'],
];

export default function Dashboard() {
    const { post } = useForm();

    return (
        <>
            <Head title="Overview" />
            <main className="min-h-screen bg-[#10161d]">
                <header className="border-b border-white/10 bg-[#141d26]">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-300">Rogerlab</p>
                            <h1 className="mt-1 text-xl font-semibold text-white">Server watch</h1>
                        </div>
                        <button onClick={() => post('/logout')} className="text-sm text-slate-400 hover:text-white">Cerrar sesion</button>
                    </div>
                </header>
                <section className="mx-auto max-w-7xl px-6 py-12">
                    <div className="mb-10 max-w-2xl">
                        <p className="text-sm font-medium text-emerald-300">152.228.234.57 · operativo</p>
                        <h2 className="mt-3 text-4xl font-semibold tracking-tight text-white">Una vista tranquila de tu infraestructura.</h2>
                        <p className="mt-4 text-slate-400">Los recolectores trabajan en segundo plano. Esta consola solo presenta snapshots y acciones permitidas.</p>
                    </div>
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
                </section>
            </main>
        </>
    );
}

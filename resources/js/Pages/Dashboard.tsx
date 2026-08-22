import { Head, Link, useForm } from '@inertiajs/react';

type Metric = { snapshot: Record<string, any>; captured_at: string } | null;
type Container = { id: number; name: string; image: string | null; status: string; captured_at: string };
type UptimeCheck = { subdomain: { name: string; url: string } | null; available: boolean; status_code: number | null; response_time_ms: number | null };
type Execution = { id: number; workflow_name: string | null; status: string | null; error: string | null };
type SeoCheck = { id: number; subdomain: { name: string } | null; results: Record<string, any> };
type Alert = { id: number; type: string; severity: string; triggered_at: string | null };

type Props = {
    serverIp?: string;
    metrics: Metric;
    containers: Container[];
    uptime: UptimeCheck[];
    executions: Execution[];
    seo: SeoCheck[];
    alerts: Alert[];
    updatedAt: string;
};

const modules = [
    ['System metrics', 'CPU, memoria, disco y red', '/'],
    ['Docker', 'Contenedores y acciones auditadas', '/docker'],
    ['Uptime', 'Disponibilidad de tus subdominios', '/uptime'],
    ['SEO', 'Salud tecnica por dominio', '/seo'],
    ['n8n', 'Ejecuciones y workflows fallidos', '/n8n'],
];

export default function Dashboard({ serverIp, metrics, containers, uptime, executions, seo, alerts, updatedAt }: Props) {
    const { post } = useForm();
    const memory = metrics?.snapshot.memory;
    const memoryUsed = memory?.total_kb && memory?.available_kb
        ? Math.round((1 - memory.available_kb / memory.total_kb) * 100)
        : null;

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
                        <p className="text-sm font-medium text-emerald-300">{serverIp ?? 'Servidor'} · operativo</p>
                        <h2 className="mt-3 text-4xl font-semibold tracking-tight text-white">Una vista tranquila de tu infraestructura.</h2>
                        <p className="mt-4 text-slate-400">Ultima actualizacion: {new Date(updatedAt).toLocaleString('es-ES')}</p>
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
                <div className="mb-10 grid gap-4 sm:grid-cols-3">
                    <Stat label="Carga 1 min" value={metrics?.snapshot.load_1m ?? 'Sin datos'} />
                    <Stat label="Memoria usada" value={memoryUsed === null ? 'Sin datos' : `${memoryUsed}%`} />
                    <Stat label="Contenedores" value={containers.length} />
                </div>
                <section className="mt-10 grid gap-6 lg:grid-cols-2">
                    <DataPanel title="Uptime reciente">
                        {uptime.length ? uptime.map((check) => <Row key={check.subdomain?.name} label={check.subdomain?.name ?? 'Sin nombre'} value={check.available ? `${check.status_code} · ${check.response_time_ms ?? '-'} ms` : 'No disponible'} danger={!check.available} />) : <Empty />}
                    </DataPanel>
                    <DataPanel title="Contenedores">
                        {containers.length ? containers.map((container) => <Row key={`${container.id}-${container.captured_at}`} label={container.name} value={container.status} danger={/exited|unhealthy/i.test(container.status)} />) : <Empty />}
                    </DataPanel>
                    <DataPanel title="Ejecuciones n8n">
                        {executions.length ? executions.map((execution) => <Row key={execution.id} label={execution.workflow_name ?? 'Workflow'} value={execution.status ?? 'Sin estado'} danger={execution.status === 'error' || execution.status === 'failed'} />) : <Empty />}
                    </DataPanel>
                    <DataPanel title="Alertas activas">
                        {alerts.length ? alerts.map((alert) => <Row key={alert.id} label={alert.type} value={alert.severity} danger={alert.severity === 'critical'} />) : <Empty text="No hay alertas activas." />}
                    </DataPanel>
                        <DataPanel title="SEO reciente">
                            {seo.length ? seo.map((check) => <Row key={check.id} label={check.subdomain?.name ?? 'Sin nombre'} value={check.results.error ? 'Error' : `${check.results.status_code ?? '-'} · ${check.results.title ? 'title OK' : 'sin title'}`} danger={Boolean(check.results.error)} />) : <Empty />}
                        </DataPanel>
                </section>
            </main>
        </>
    );
}

function Stat({ label, value }: { label: string; value: string | number }) {
    return <div className="border border-white/10 bg-[#18232e] p-5"><p className="text-xs uppercase tracking-wider text-slate-500">{label}</p><p className="mt-2 text-2xl font-semibold text-white">{value}</p></div>;
}

function DataPanel({ title, children }: { title: string; children: React.ReactNode }) {
    return <div className="border border-white/10 bg-[#141d26] p-6"><h3 className="font-semibold text-white">{title}</h3><div className="mt-4 divide-y divide-white/10">{children}</div></div>;
}

function Row({ label, value, danger = false }: { label: string; value: string | number; danger?: boolean }) {
    return <div className="flex items-center justify-between gap-4 py-3 text-sm"><span className="truncate text-slate-300">{label}</span><span className={danger ? 'text-rose-300' : 'text-emerald-300'}>{value}</span></div>;
}

function Empty({ text = 'A la espera del primer snapshot.' }: { text?: string }) {
    return <p className="py-4 text-sm text-slate-500">{text}</p>;
}

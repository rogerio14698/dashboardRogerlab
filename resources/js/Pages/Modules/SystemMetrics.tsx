import { Head, Link } from '@inertiajs/react';

type Metric = { snapshot: Record<string, any>; captured_at: string } | null;

export default function SystemMetrics({ metric = null }: { metric?: Metric }) {
    const memory = metric?.snapshot.memory;
    const memoryUsed = memory?.total_kb && memory?.available_kb
        ? Math.round((1 - memory.available_kb / memory.total_kb) * 100)
        : null;

    return <><Head title="System metrics" /><main className="min-h-screen bg-[#10161d] p-8 text-white"><Link href="/" className="text-sm text-cyan-300">&lt;- Overview</Link><h1 className="mt-10 text-4xl font-semibold">System metrics</h1><p className="mt-3 text-slate-400">Recursos del host recogidos directamente desde el servidor.</p><div className="mt-10 grid gap-4 sm:grid-cols-3"><Stat label="Carga 1 min" value={metric?.snapshot.load_1m ?? 'Sin datos'} /><Stat label="Carga 5 min" value={metric?.snapshot.load_5m ?? 'Sin datos'} /><Stat label="Memoria usada" value={memoryUsed === null ? 'Sin datos' : `${memoryUsed}%`} /></div><div className="mt-6 border border-white/10 bg-[#141d26] p-6 text-sm text-slate-400">{metric ? `Snapshot capturado: ${new Date(metric.captured_at).toLocaleString('es-ES')}` : 'A la espera del primer snapshot. Ejecuta php8.3 artisan monitor:system.'}</div></main></>;
}

function Stat({ label, value }: { label: string; value: string | number }) {
    return <div className="border border-white/10 bg-[#18232e] p-5"><p className="text-xs uppercase tracking-wider text-slate-500">{label}</p><p className="mt-2 text-2xl font-semibold text-white">{value}</p></div>;
}
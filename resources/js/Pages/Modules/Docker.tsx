import { Head, Link } from '@inertiajs/react';

type Container = { container_id: string; name: string; image: string | null; status: string; captured_at: string };

export default function Docker({ containers = [] }: { containers?: Container[] }) {
	return <><Head title="Docker" /><main className="min-h-screen bg-[#10161d] p-8 text-white"><Link href="/" className="text-sm text-cyan-300">&lt;- Overview</Link><h1 className="mt-10 text-4xl font-semibold">Docker</h1><p className="mt-3 text-slate-400">Snapshots y acciones seguras sobre el Engine API.</p><div className="mt-10 border border-white/10 bg-[#141d26] p-6"><h2 className="font-semibold">Contenedores recientes</h2>{containers.length ? <div className="mt-4 divide-y divide-white/10">{containers.map((container) => <div key={`${container.container_id}-${container.captured_at}`} className="flex items-center justify-between gap-4 py-4"><div><p className="text-slate-200">{container.name}</p><p className="text-xs text-slate-500">{container.image ?? 'Imagen no disponible'}</p></div><span className={/exited|unhealthy/i.test(container.status) ? 'text-rose-300' : 'text-emerald-300'}>{container.status}</span></div>)}</div> : <p className="mt-4 text-sm text-slate-500">A la espera del primer snapshot. Ejecuta <code>php8.3 artisan monitor:docker</code>.</p>}</div></main></>;
}

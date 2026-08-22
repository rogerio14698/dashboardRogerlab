import { Head, Link } from '@inertiajs/react';

type Check = { id: number; subdomain: { name: string } | null; results: Record<string, any> };

export default function Seo({ checks = [] }: { checks?: Check[] }) {
	return <><Head title="SEO" /><main className="min-h-screen bg-[#10161d] p-8 text-white"><Link href="/" className="text-sm text-cyan-300">&lt;- Overview</Link><h1 className="mt-10 text-4xl font-semibold">SEO</h1><p className="mt-3 text-slate-400">Robots, sitemap y metadatos historicos por subdominio.</p><div className="mt-10 border border-white/10 bg-[#141d26] p-6"><h2 className="font-semibold">Chequeos recientes</h2>{checks.length ? <div className="mt-4 divide-y divide-white/10">{checks.map((check) => <div key={check.id} className="flex items-center justify-between gap-4 py-4"><div><p className="text-slate-200">{check.subdomain?.name ?? 'Subdominio'}</p><p className="text-xs text-slate-500">{check.results.title || 'Sin title'} · {check.results.meta_description ? 'description OK' : 'sin description'}</p></div><span className={check.results.error ? 'text-rose-300' : 'text-emerald-300'}>{check.results.error ? 'Error' : check.results.status_code ?? 'OK'}</span></div>)}</div> : <p className="mt-4 text-sm text-slate-500">A la espera del primer snapshot. Ejecuta <code>php8.3 artisan monitor:seo</code>.</p>}</div></main></>;
}

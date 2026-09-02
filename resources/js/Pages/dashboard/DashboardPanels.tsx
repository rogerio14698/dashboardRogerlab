import type { ReactNode } from 'react';

// Tarjeta pequena para mostrar una metrica destacada.
export function Stat({ label, value }: { label: string; value: string | number }) {
    return <div className="border border-white/10 bg-[#18232e] p-5"><p className="text-xs uppercase tracking-wider text-slate-500">{label}</p><p className="mt-2 text-2xl font-semibold text-white">{value}</p></div>;
}

// Contenedor estandar para cada lista de estado del dashboard.
export function DataPanel({ title, children }: { title: string; children: ReactNode }) {
    return <div className="border border-white/10 bg-[#141d26] p-6"><h3 className="font-semibold text-white">{title}</h3><div className="mt-4 divide-y divide-white/10">{children}</div></div>;
}

// Fila de una lista: el color indica si el elemento esta correcto o requiere atencion.
export function Row({ label, value, danger = false }: { label: string; value: string | number; danger?: boolean }) {
    return <div className="flex items-center justify-between gap-4 py-3 text-sm"><span className="truncate text-slate-300">{label}</span><span className={danger ? 'text-rose-300' : 'text-emerald-300'}>{value}</span></div>;
}

// Mensaje de reserva cuando una fuente todavia no ha aportado datos.
export function Empty({ text = 'A la espera del primer snapshot.' }: { text?: string }) {
    return <p className="py-4 text-sm text-slate-500">{text}</p>;
}
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { DashboardHeader } from './dashboard/DashboardHeader';

// La forma que el controlador obtiene de DESCRIBE tabla para definir cada columna de la interfaz.
type Column = { name: string; type: string; nullable: boolean; key: string; default: string | null; extra: string };
type Pagination = { currentPage: number; lastPage: number; perPage: number; total: number };
type Props = { databases: string[]; database: string; tables: string[]; table: string; columns: Column[]; rows: Record<string, unknown>[]; pagination: Pagination | null };

// Página de administración: Laravel aporta datos y React sólo representa y envía las acciones del usuario.
export default function DataBase({ databases, database, tables, table, columns, rows, pagination }: Props) {
    const { post } = useForm();
    const [search, setSearch] = useState('');
    const [editingRow, setEditingRow] = useState<Record<string, unknown> | null>(null);
    const primaryKey = columns.find((column) => column.key === 'PRI')?.name;

    // Cambiar de base reinicia la tabla para que Laravel cargue su lista de tablas.
    function selectDatabase(nextDatabase: string) {
        router.get('/data-base', { database: nextDatabase }, { preserveState: false });
    }

    // Cambiar de tabla solicita tanto DESCRIBE como la primera página de filas.
    function selectTable(nextTable: string) {
        router.get('/data-base', { database, table: nextTable }, { preserveState: false });
    }

    // La búsqueda se ejecuta al enviar el formulario, no en cada pulsación de tecla.
    function submitSearch(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get('/data-base', { database, table, search }, { preserveState: true, replace: true });
    }

    // Redirige a otra página, conservando la base, tabla y texto de búsqueda activos.
    function goToPage(page: number) {
        router.get('/data-base', { database, table, search, page }, { preserveState: true, replace: true });
    }

    // Envía el valor de la clave primaria para que el backend elimine exactamente esa fila.
    function deleteRow(row: Record<string, unknown>) {
        if (!primaryKey || !window.confirm('Esta accion eliminara el registro de forma permanente.')) return;
        router.delete('/data-base/row', { data: { database, table, primary_value: row[primaryKey] } });
    }

    return (
        <>
            <Head title="Rogerlab | Base de datos" />
            <main className="min-h-screen bg-[#10161d] text-slate-200">
                {/* Navegador común a Inicio, Métricas, Dominios y esta página. */}
                <DashboardHeader onLogout={() => post('/logout')} />

                <section className="mx-auto grid max-w-7xl gap-6 px-6 py-8 lg:grid-cols-[240px_minmax(0,1fr)]">
                    {/* Selector de base y lista de tablas: los valores provienen siempre de MariaDB. */}
                    <aside className="border border-white/10 bg-[#141d26] p-4">
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400" htmlFor="database">Base de datos</label>
                        <select id="database" value={database} onChange={(event) => selectDatabase(event.target.value)} className="mt-2 w-full border border-white/10 bg-[#18232e] px-3 py-2 text-sm text-white outline-none focus:border-cyan-300">
                            <option value="">Selecciona una base</option>
                            {databases.map((name) => <option key={name} value={name}>{name}</option>)}
                        </select>

                        <h2 className="mt-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Tablas</h2>
                        <div className="mt-2 max-h-[60vh] overflow-y-auto">
                            {tables.map((name) => <button key={name} onClick={() => selectTable(name)} className={`block w-full px-3 py-2 text-left text-sm ${name === table ? 'bg-cyan-300/15 text-cyan-200' : 'text-slate-400 hover:bg-white/5 hover:text-white'}`}>{name}</button>)}
                            {database && tables.length === 0 && <p className="px-3 py-2 text-sm text-slate-500">No hay tablas.</p>}
                        </div>
                    </aside>

                    {/* Área de contenido: controles, resultados y paginación para la tabla seleccionada. */}
                    <div className="min-w-0">
                        <div className="flex flex-wrap items-end justify-between gap-4">
                            <div><p className="text-xs font-semibold uppercase tracking-wider text-cyan-300">Explorador MariaDB</p><h1 className="mt-1 text-2xl font-semibold text-white">{table || 'Selecciona una tabla'}</h1></div>
                            {table && <button onClick={() => setEditingRow({})} className="border border-cyan-300 bg-cyan-300 px-4 py-2 text-sm font-semibold text-[#10161d]">Nuevo registro</button>}
                        </div>

                        {table && <form onSubmit={submitSearch} className="mt-6 flex gap-2"><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Buscar en todas las columnas" className="w-full border border-white/10 bg-[#18232e] px-3 py-2 text-sm text-white outline-none focus:border-cyan-300" /><button className="border border-white/10 px-4 py-2 text-sm hover:border-cyan-300">Buscar</button></form>}

                        {table ? <div className="mt-4 overflow-x-auto border border-white/10 bg-[#141d26]"><table className="w-full text-left text-sm"><thead className="bg-white/5 text-xs uppercase tracking-wider text-slate-400"><tr>{columns.map((column) => <th key={column.name} className="whitespace-nowrap px-4 py-3">{column.name}<span className="ml-1 normal-case text-slate-600">{column.type}</span></th>)}<th className="px-4 py-3">Acciones</th></tr></thead><tbody className="divide-y divide-white/10">{rows.map((row, index) => <tr key={primaryKey ? String(row[primaryKey]) : index} className="hover:bg-white/5">{columns.map((column) => <td key={column.name} className="max-w-xs truncate px-4 py-3 text-slate-300" title={String(row[column.name] ?? '')}>{formatValue(row[column.name])}</td>)}<td className="whitespace-nowrap px-4 py-3">{primaryKey ? <div className="flex gap-3"><button onClick={() => setEditingRow(row)} className="text-cyan-300 hover:text-cyan-200">Editar</button><button onClick={() => deleteRow(row)} className="text-rose-300 hover:text-rose-200">Eliminar</button></div> : <span className="text-slate-500">Sin clave primaria</span>}</td></tr>)}{rows.length === 0 && <tr><td colSpan={columns.length + 1} className="px-4 py-8 text-center text-slate-500">No se encontraron registros.</td></tr>}</tbody></table></div> : <div className="mt-6 border border-dashed border-white/10 p-10 text-center text-slate-500">Selecciona una base de datos y después una tabla.</div>}

                        {pagination && <div className="mt-4 flex items-center justify-between text-sm text-slate-400"><span>{pagination.total} registros</span><div className="flex items-center gap-3"><button disabled={pagination.currentPage === 1} onClick={() => goToPage(pagination.currentPage - 1)} className="disabled:text-slate-700">Anterior</button><span>{pagination.currentPage} / {pagination.lastPage}</span><button disabled={pagination.currentPage === pagination.lastPage} onClick={() => goToPage(pagination.currentPage + 1)} className="disabled:text-slate-700">Siguiente</button></div></div>}
                    </div>
                </section>

                {/* El modal recibe las columnas reales y se adapta tanto para crear como editar una fila. */}
                {editingRow !== null && <RecordModal columns={columns} database={database} table={table} row={editingRow} primaryKey={primaryKey} onClose={() => setEditingRow(null)} />}
            </main>
        </>
    );
}

// Modal de formulario: cada campo se genera a partir de DESCRIBE y no de una estructura escrita a mano.
function RecordModal({ columns, database, table, row, primaryKey, onClose }: { columns: Column[]; database: string; table: string; row: Record<string, unknown>; primaryKey?: string; onClose: () => void }) {
    const editing = Object.keys(row).length > 0;
    const { data, setData, post, patch, processing, errors } = useForm<{ database: string; table: string; primary_value: unknown; values: Record<string, unknown> }>({ database, table, primary_value: primaryKey ? row[primaryKey] : '', values: Object.fromEntries(columns.filter((column) => !column.extra.includes('auto_increment')).map((column) => [column.name, row[column.name] ?? column.default ?? ''])) });
    const editableColumns = columns.filter((column) => !column.extra.includes('auto_increment') && (!editing || column.name !== primaryKey));

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = { onSuccess: onClose };
        editing ? patch('/data-base/row', options) : post('/data-base/row', options);
    }

    return <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"><form onSubmit={submit} className="max-h-[90vh] w-full max-w-2xl overflow-y-auto border border-white/10 bg-[#141d26] p-6"><div className="flex items-center justify-between"><h2 className="text-lg font-semibold text-white">{editing ? 'Editar registro' : 'Nuevo registro'}</h2><button type="button" onClick={onClose} className="text-slate-400 hover:text-white">Cerrar</button></div><div className="mt-6 grid gap-4 sm:grid-cols-2">{editableColumns.map((column) => <label key={column.name} className="block text-sm text-slate-300">{column.name}<span className="ml-1 text-xs text-slate-500">{column.type}</span><ColumnInput column={column} value={data.values[column.name]} onChange={(value) => setData('values', { ...data.values, [column.name]: value })} /></label>)}</div>{errors.values && <p className="mt-4 text-sm text-rose-300">{errors.values}</p>}<div className="mt-6 flex justify-end gap-3"><button type="button" onClick={onClose} className="px-4 py-2 text-sm text-slate-400">Cancelar</button><button disabled={processing} className="bg-cyan-300 px-4 py-2 text-sm font-semibold text-[#10161d] disabled:opacity-50">{editing ? 'Guardar cambios' : 'Crear registro'}</button></div></form></div>;
}

// El tipo SQL guía el control HTML: booleanos usan selector y textos largos un área de texto.
function ColumnInput({ column, value, onChange }: { column: Column; value: unknown; onChange: (value: string) => void }) {
    const baseClass = 'mt-1 w-full border border-white/10 bg-[#18232e] px-3 py-2 text-sm text-white outline-none focus:border-cyan-300';
    if (/tinyint\(1\)|boolean/i.test(column.type)) return <select value={String(value ?? '')} onChange={(event) => onChange(event.target.value)} className={baseClass}><option value="">Sin valor</option><option value="1">Si</option><option value="0">No</option></select>;
    if (/text|json/i.test(column.type)) return <textarea value={String(value ?? '')} onChange={(event) => onChange(event.target.value)} className={baseClass} rows={3} />;
    const type = /int|decimal|float|double/i.test(column.type) ? 'number' : /date|time/i.test(column.type) ? 'datetime-local' : 'text';
    return <input type={type} value={String(value ?? '')} onChange={(event) => onChange(event.target.value)} className={baseClass} />;
}

// Evita que objetos JSON aparezcan como "[object Object]" dentro de las celdas de la tabla.
function formatValue(value: unknown): string {
    if (value === null || value === undefined) return 'NULL';
    return typeof value === 'object' ? JSON.stringify(value) : String(value);
}
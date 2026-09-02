import { Head, useForm } from '@inertiajs/react';
import { DashboardHeader } from './dashboard/DashboardHeader';
import { DataPanel, Empty, Row, Stat } from './dashboard/DashboardPanels';
import { ModuleCards } from './dashboard/ModuleCards';
import type { DashboardProps } from './dashboard/types';

// Pagina de metricas: recibe los datos del servidor y compone los bloques visuales reutilizables.
export default function Metrics({ serverIp, metrics, containers, uptime, executions, seo, alerts, updatedAt }: DashboardProps) {
    const { post } = useForm();

    // Calcula el porcentaje de RAM usada solo si el snapshot trae ambos valores necesarios.
    const memory = metrics?.snapshot.memory;
    const memoryUsed = memory?.total_kb && memory?.available_kb
        ? Math.round((1 - memory.available_kb / memory.total_kb) * 100)
        : null;

    return (
        <>
            <Head title="Rogerlab | Dashboard del servidor" />
            <main className="min-h-screen bg-[#10161d]">
                {/* Cabecera con navegacion y accion para terminar la sesion. */}
                <DashboardHeader onLogout={() => post('/logout')} />

                {/* Estado general y accesos rapidos a cada modulo. */}
                <section className="mx-auto max-w-7xl px-6 py-12">
                    <div className="mb-10 max-w-2xl">
                        <p className="text-sm font-medium text-emerald-300">{serverIp ?? 'Servidor'} · operativo</p>
                        <p className="mt-4 text-slate-400">Ultima actualizacion: {new Date(updatedAt).toLocaleString('es-ES')}</p>
                    </div>
                    <ModuleCards />
                </section>

                {/* Metricas resumidas para consultar el estado del servidor de un vistazo. */}
                <div className="mb-10 grid gap-4 sm:grid-cols-3">
                    <Stat label="Carga 1 min" value={metrics?.snapshot.load_1m ?? 'Sin datos'} />
                    <Stat label="Memoria usada" value={memoryUsed === null ? 'Sin datos' : `${memoryUsed}%`} />
                    <Stat label="Contenedores" value={containers.length} />
                </div>

                {/* Paneles que listan los ultimos resultados de cada servicio monitorizado. */}
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

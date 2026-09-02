// Tipos de los datos que Laravel entrega a la pagina mediante Inertia.
export type Metric = { snapshot: Record<string, any>; captured_at: string } | null;
export type Container = { id: number; name: string; image: string | null; status: string; captured_at: string };
export type UptimeCheck = { subdomain: { name: string; url: string } | null; available: boolean; status_code: number | null; response_time_ms: number | null };
export type Execution = { id: number; workflow_name: string | null; status: string | null; error: string | null };
export type SeoCheck = { id: number; subdomain: { name: string } | null; results: Record<string, any> };
export type Alert = { id: number; type: string; severity: string; triggered_at: string | null };

// Contrato completo de propiedades que recibe Dashboard.tsx.
export type DashboardProps = {
    serverIp?: string;
    metrics: Metric;
    containers: Container[];
    uptime: UptimeCheck[];
    executions: Execution[];
    seo: SeoCheck[];
    alerts: Alert[];
    updatedAt: string;
};
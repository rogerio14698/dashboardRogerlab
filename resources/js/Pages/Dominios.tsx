import { Head, useForm } from '@inertiajs/react';
import { DashboardHeader } from './dashboard/DashboardHeader';

interface DomainRecord {
    id: string;
    name: string;
    type: string;
    content: string;
    proxied: boolean;
    created_on: string;
}

interface Props {
    dominios: DomainRecord[];
}

export default function Dominios({ dominios }: Props) {
    const { post } = useForm();

    return (
        <>
            <Head title="Rogerlab | Dominios" />
            <main className="min-h-screen bg-[#10161d]">
                {/* Cabecera compartida con las demas vistas de la aplicacion. */}
                <DashboardHeader onLogout={() => post('/logout')} />

                {/* Tabla de los registros DNS recibidos y transformados desde Cloudflare. */}
                {/* Nota, mas adelatante quiero ampliar esta zona y añadir un CRUD, pero para ello necesito
                modificar las clave API de Cloudflare para que pueda escribir y editar los dominios.*/}

                {/**Tambien quiero poner un pequeño form para añadir nuevos dominios y subdominios.
                 * Tambien estaría bien bien poder configurar las DNS, los proxies y los certificados SSL.
                 * 
                 * Ver también si puedo añadir un pequeño panel de control para cada dominio, con un resumen de su estado y sus registros DNS.
                 * Ver tambien cuanta gente ha visitado cada dominio y subdominio, y si hay alertas o problemas de disponibilidad.
                 * 
                 * 
                 */}
                <section className="mx-auto max-w-7xl px-6 py-12">
                    <h2 className="mb-4 text-2xl font-bold text-white">Dominios y Subdominios (Cloudflare)</h2>
                    <div className="overflow-x-auto border border-slate-700 bg-slate-900">
                        <table className="w-full text-left text-sm text-slate-300">
                            <thead className="bg-slate-800 text-xs uppercase text-slate-400">
                                <tr>
                                    <th className="px-4 py-3">Nombre / Subdominio</th>
                                    <th className="px-4 py-3">Tipo</th>
                                    <th className="px-4 py-3">Destino / IP</th>
                                    <th className="px-4 py-3">Proxy Cloudflare</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800">
                                {dominios.map((domain) => (
                                    <tr key={domain.id} className="hover:bg-slate-800/50">
                                        <td className="px-4 py-3 font-medium text-white">{domain.name}</td>
                                        <td className="px-4 py-3">
                                            <span className="rounded bg-slate-700 px-2 py-0.5 text-xs">
                                                {domain.type}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 font-mono">{domain.content}</td>
                                        <td className="px-4 py-3">
                                            {domain.proxied ? (
                                                <span className="text-orange-400">Proxied (Nube)</span>
                                            ) : (
                                                <span className="text-slate-500">Solo DNS</span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </>
    );
}
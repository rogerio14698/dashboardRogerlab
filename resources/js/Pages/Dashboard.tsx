// importa los componentes de React y los hooks de Inertia.js necesarios para la pagina de inicio.
import { Head, useForm } from '@inertiajs/react';
import { DashboardHeader } from './dashboard/DashboardHeader';


// Tipos de propiedades que recibe la pagina de inicio.
type DashboardProps = {
    title: string;
    description: string;
    serverIp?: string;
    updatedAt: string;
};

// Pagina de inicio. Solo compone contenido propio y el navegador reutilizable.
export default function Dashboard({ title, description, serverIp, updatedAt }: DashboardProps) {
    const { post } = useForm();

    return (
        <>
            <Head title={`Rogerlab | ${title}`} />
            <main className="min-h-screen bg-[#10161d]">
                {/* El mismo componente puede importarse en cualquier pagina React. */}
                <DashboardHeader onLogout={() => post('/logout')} />

                {/* Contenido exclusivo de la pagina de inicio. */}
                <section className="mx-auto max-w-7xl px-6 py-12">
                    <h2 className="mt-2 text-3xl font-semibold text-white">{title}</h2>
                   
                    <p className="text-sm font-medium text-emerald-300">{serverIp ?? 'Servidor'} · operativo</p>

                    {/*Ver por ultimo como hacer esto:  */}
                    <p className="mt-4 text-slate-400">Ultimo Ping: {new Date(updatedAt).toLocaleString('es-ES')}</p>
                    <h4 className="text-white">Ulimo cambio </h4>

                </section>
            </main>
        </>
    );
}
import { Head, useForm } from '@inertiajs/react';

export default function Login() {
    const form = useForm({ email: '', password: '', remember: false });

    function submit(event: React.FormEvent) {
        event.preventDefault();
        form.post('/login');
    }

    return (
        <main className="flex min-h-screen items-center justify-center bg-[#10161d] px-6">
            <Head title="Acceso" />
            <form onSubmit={submit} className="w-full max-w-md border border-white/10 bg-[#18232e] p-8">
                <p className="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-300">Rogerlab</p>
                <h1 className="mt-3 text-3xl font-semibold text-white">Acceso privado</h1>
                <p className="mt-2 text-sm text-slate-400">Solo el administrador puede entrar.</p>
                <label className="mt-8 block text-sm text-slate-300">Email<input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} className="mt-2 w-full border border-white/10 bg-[#10161d] px-3 py-2 text-white outline-none focus:border-cyan-300" /></label>
                <label className="mt-4 block text-sm text-slate-300">Contrasena<input type="password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} className="mt-2 w-full border border-white/10 bg-[#10161d] px-3 py-2 text-white outline-none focus:border-cyan-300" /></label>
                {form.errors.email && <p className="mt-3 text-sm text-rose-300">{form.errors.email}</p>}
                <button disabled={form.processing} className="mt-6 w-full bg-cyan-300 px-4 py-3 font-semibold text-slate-950 hover:bg-cyan-200">Entrar</button>
            </form>
        </main>
    );
}

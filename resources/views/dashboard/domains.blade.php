@extends('layouts.app')

@section('content')
<div class="page">
    @include('partials.header')
    <main class="page__content">
        <h2>Dominios y Subdominios (Cloudflare)</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nombre / Subdominio</th><th>Tipo</th><th>Destino / IP</th><th>Proxy Cloudflare</th></tr></thead>
                <tbody>
                @forelse ($dominios as $domain)
                    <tr><td><strong>{{ $domain['name'] }}</strong></td><td>{{ $domain['type'] }}</td><td><code>{{ $domain['content'] }}</code></td><td class="{{ $domain['proxied'] ? 'status--ok' : 'muted' }}">{{ $domain['proxied'] ? 'Proxied (Nube)' : 'Solo DNS' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="muted">No hay registros DNS disponibles.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection

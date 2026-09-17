@extends('layouts.app')

@section('content')
    <div class="pagina">
        @include('partials.header')
        <main class="paginaContenido">
            <h2>Dominios y Subdominios (Cloudflare)</h2>
            <div class="envolturaTabla">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre / Subdominio</th>
                            <th>Tipo</th>
                            <th>Destino / IP</th>
                            <th>Proxy Cloudflare</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dominios as $domain)
                            <tr>
                                <td><strong>{{ $domain['name'] }}</strong></td>
                                <td>{{ $domain['type'] }}</td>
                                <td><code>{{ $domain['content'] }}</code></td>
                                <td class="{{ $domain['proxied'] ? 'estadoCorrecto' : 'textoSuave' }}">
                                    {{ $domain['proxied'] ? 'Proxied (Nube)' : 'Solo DNS' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="textoSuave">No hay registros DNS disponibles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
@endsection

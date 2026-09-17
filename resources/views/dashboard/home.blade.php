@extends('layouts.app')

@section('content')
<div class="pagina">
    @include('partials.header')
    <main class="paginaContenido">
        <div class="introduccionPanel">
            <p class="etiquetaSuperior">{{ $title }}</p>
            <h2>{{ $description }}</h2>
            <p class="estadoCorrecto">{{ $serverIp ?? 'Servidor' }} · operativo</p>
            <p class="textoSuave">Ultimo ping: {{ \Illuminate\Support\Carbon::parse($updatedAt)->locale('es')->isoFormat('L LTS') }}</p>
        </div>
        <div class="rejillaTarjetas">
            <a class="tarjeta tarjetaModulo" href="{{ route('metrics') }}">
                <strong>Metricas</strong>
                <p class="textoSuave">CPU, memoria y estado general.</p>
            </a>
            {{--  
            
            --}}
              <a class="tarjeta tarjetaModulo" href="#">
                <strong>Hacer backup completo</strong>
                <p class="textoSuave">Realiza un backup completo del sistema.</p>
            </a>

            <a class="tarjeta tarjetaModulo" href="{{ route('domains.index') }}">
                <strong>Domains</strong>
                <p class="textoSuave">Registros DNS de Cloudflare.</p>
            </a>
            <a class="tarjeta tarjetaModulo" href="{{ route('database.index') }}">
                <strong>Database</strong>
                <p class="textoSuave">Explorador y operaciones CRUD.</p>
            </a>
        </div>
    </main>
</div>
@endsection

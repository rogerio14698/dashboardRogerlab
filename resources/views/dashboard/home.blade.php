@extends('layouts.app')

@section('content')
<div class="page">
    @include('partials.header')
    <main class="page__content">
        <div class="dashboard__intro">
            <p class="eyebrow">{{ $title }}</p>
            <h2>{{ $description }}</h2>
            <p class="status--ok">{{ $serverIp ?? 'Servidor' }} · operativo</p>
            <p class="muted">Ultimo ping: {{ \Illuminate\Support\Carbon::parse($updatedAt)->locale('es')->isoFormat('L LTS') }}</p>
        </div>
        <div class="card-grid">
            <a class="card module-card" href="{{ route('metrics') }}">
                <strong>Metricas</strong>
                <p class="muted">CPU, memoria y estado general.</p>
            </a>
            {{--  
            
            --}}
              <a class="card module-card" href="#">
                <strong>Hacer backup completo</strong>
                <p class="muted">Realiza un backup completo del sistema.</p>
            </a>

            <a class="card module-card" href="{{ route('domains.index') }}">
                <strong>Domains</strong>
                <p class="muted">Registros DNS de Cloudflare.</p>
            </a>
            <a class="card module-card" href="{{ route('database.index') }}">
                <strong>Database</strong>
                <p class="muted">Explorador y operaciones CRUD.</p>
            </a>
        </div>
    </main>
</div>
@endsection

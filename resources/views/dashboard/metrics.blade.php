@extends('layouts.app')

@section('content')
<div class="pagina">
    @include('partials.header')
    <main class="paginaContenido">
        <div class="introduccionPanel">
            {{-- Introducción del dashboard de métricas --}}
            <p class="estadoCorrecto">{{ $serverIp ?? 'Servidor' }} · operativo</p>
            <p class="textoSuave">Ultima actualizacion: {{ \Illuminate\Support\Carbon::parse($updatedAt)->locale('es')->isoFormat('L LTS') }}</p>
        </div>
        @php
            $memory = data_get($metrics, 'snapshot.memory');
            $memoryUsed = data_get($memory, 'total_kb') && data_get($memory, 'available_kb') ? round((1 - data_get($memory, 'available_kb') / data_get($memory, 'total_kb')) * 100) : null;
        @endphp
        <div class="rejillaTarjetas">
            <!-- Acción para hacer snapshot completo del servidor -->
            <a class="tarjeta tarjetaModulo" href="#">Hacer Snapshot completo del servidor</a>
            <!-- Estadísticas del servidor -->
            <div class="tarjeta"><span class="etiquetaEstadistica">Carga 1 min</span><div class="valorEstadistica">{{ data_get($metrics, 'snapshot.load_1m', 'Sin datos') }}</div></div>
            <div class="tarjeta"><span class="etiquetaEstadistica">Memoria usada</span><div class="valorEstadistica">{{ $memoryUsed === null ? 'Sin datos' : $memoryUsed . '%' }}</div></div>
            <div class="tarjeta"><span class="etiquetaEstadistica">Contenedores</span><div class="valorEstadistica">{{ $containers->count() }}</div></div>
        </div>
        <div class="panelesPanel">
        {{-- Datos del servidor --}}
            <section class="panel"><h3>Uptime reciente</h3><div class="data-list">@forelse ($uptime as $check)<div class="data-list__row"><span>{{ $check->subdomain?->name ?? 'Sin nombre' }}</span><span class="{{ $check->available ? 'status--ok' : 'status--danger' }}">{{ $check->available ? $check->status_code . ' · ' . ($check->response_time_ms ?? '-') . ' ms' : 'No disponible' }}</span></div>@empty<p class="muted">A la espera del primer snapshot.</p>@endforelse</div></section>
            <section class="panel"><h3>Contenedores</h3><div class="data-list">@forelse ($containers as $container)<div class="data-list__row"><span>{{ $container->name }}</span><span class="{{ preg_match('/exited|unhealthy/i', $container->status) ? 'status--danger' : 'status--ok' }}">{{ $container->status }}</span></div>@empty<p class="muted">A la espera del primer snapshot.</p>@endforelse</div></section>
            <section class="panel"><h3>Ejecuciones n8n</h3><div class="data-list">@forelse ($executions as $execution)<div class="data-list__row"><span>{{ $execution->workflow_name ?? 'Workflow' }}</span><span class="{{ in_array($execution->status, ['error', 'failed'], true) ? 'status--danger' : 'status--ok' }}">{{ $execution->status ?? 'Sin estado' }}</span></div>@empty<p class="muted">A la espera del primer snapshot.</p>@endforelse</div></section>
            <section class="panel"><h3>Alertas activas</h3><div class="data-list">@forelse ($alerts as $alert)<div class="data-list__row"><span>{{ $alert->type }}</span><span class="{{ $alert->severity === 'critical' ? 'status--danger' : 'status--ok' }}">{{ $alert->severity }}</span></div>@empty<p class="muted">No hay alertas activas.</p>@endforelse</div></section>
            <section class="panel"><h3>SEO reciente</h3><div class="data-list">@forelse ($seo as $check)<div class="data-list__row"><span>{{ $check->subdomain?->name ?? 'Sin nombre' }}</span><span class="{{ data_get($check->results, 'error') ? 'status--danger' : 'status--ok' }}">{{ data_get($check->results, 'error') ? 'Error' : (data_get($check->results, 'status_code', '-') . ' · ' . (data_get($check->results, 'title') ? 'title OK' : 'sin title')) }}</span></div>@empty<p class="muted">A la espera del primer snapshot.</p>@endforelse</div></section>
        </div>
    </main>
</div>
@endsection

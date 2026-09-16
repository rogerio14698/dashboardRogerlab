@extends('layouts.app')

@section('content')
    @php
        $moduleData = [
            'system-metrics' => [
                'title' => 'System metrics',
                'description' => 'Recursos del host recogidos directamente desde el servidor.',
            ],
            'docker' => ['title' => 'Docker', 'description' => 'Snapshots y acciones seguras sobre el Engine API.'],
            'uptime' => [
                'title' => 'Uptime',
                'description' => 'Disponibilidad y latencia de los subdominios configurados.',
            ],
            'seo' => ['title' => 'SEO', 'description' => 'Robots, sitemap y metadatos historicos por subdominio.'],
            'n8n' => ['title' => 'n8n', 'description' => 'Ejecuciones recientes y workflows que requieren atencion.'],
        ][$module];
    @endphp
    <div class="page">
        @include('partials.header')
        <main class="page__content">
            <p class="eyebrow">Monitorizacion</p>
            <h2>{{ $moduleData['title'] }}</h2>
            <p class="muted">{{ $moduleData['description'] }}</p>

            @if ($module === 'system-metrics')
                @php
                    $memory = data_get($metric, 'snapshot.memory');
                    $memoryUsed =
                        data_get($memory, 'total_kb') && data_get($memory, 'available_kb')
                            ? round((1 - data_get($memory, 'available_kb') / data_get($memory, 'total_kb')) * 100)
                            : null;
                @endphp
                <div class="card-grid" style="margin-top: 2.5rem">
                    <div class="card"><span class="stat__label">Carga 1 min</span>
                        <div class="stat__value">{{ data_get($metric, 'snapshot.load_1m', 'Sin datos') }}</div>
                    </div>
                    <div class="card"><span class="stat__label">Carga 5 min</span>
                        <div class="stat__value">{{ data_get($metric, 'snapshot.load_5m', 'Sin datos') }}</div>
                    </div>
                    <div class="card"><span class="stat__label">Memoria usada</span>
                        <div class="stat__value">{{ $memoryUsed === null ? 'Sin datos' : $memoryUsed . '%' }}</div>
                    </div>
                </div>
                <p class="panel muted" style="margin-top: 1.5rem">
                    {{ $metric ? 'Snapshot capturado: ' . $metric->captured_at->locale('es')->isoFormat('L LTS') : 'A la espera del primer snapshot. Ejecuta php8.3 artisan monitor:system.' }}
                </p>
            @elseif ($module === 'docker')
                <section class="panel" style="margin-top: 2.5rem">
                    <h3>Contenedores recientes</h3>
                    <div class="data-list">
                        @forelse ($containers as $container)
                            <div class="data-list__row">
                                <div>
                                    <div>{{ $container->name }}</div><small
                                        class="muted">{{ $container->image ?? 'Imagen no disponible' }}</small>
                                </div><span
                                    class="{{ preg_match('/exited|unhealthy/i', $container->status) ? 'status--danger' : 'status--ok' }}">{{ $container->status }}</span>
                        </div>@empty<p class="muted">A la espera del primer snapshot. Ejecuta <code>php8.3 artisan
                                    monitor:docker</code>.</p>
                        @endforelse
                    </div>
                </section>
            @elseif ($module === 'uptime')
                <section class="panel" style="margin-top: 2.5rem">
                    <h3>Chequeos recientes</h3>
                    <div class="data-list">
                        @forelse ($checks as $check)
                            <div class="data-list__row"><span>{{ $check->subdomain?->name ?? 'Subdominio' }}</span><span
                                    class="{{ $check->available ? 'status--ok' : 'status--danger' }}">{{ $check->available ? $check->status_code . ' · ' . ($check->response_time_ms ?? '-') . ' ms' : 'No disponible' }}</span>
                        </div>@empty<p class="muted">A la espera del primer snapshot. Ejecuta <code>php8.3 artisan
                                    monitor:uptime</code>.</p>
                        @endforelse
                    </div>
                </section>
            @elseif ($module === 'seo')
                <section class="panel" style="margin-top: 2.5rem">
                    <h3>Chequeos recientes</h3>
                    <div class="data-list">
                        @forelse ($checks as $check)
                            <div class="data-list__row">
                                <div>
                                    <div>{{ $check->subdomain?->name ?? 'Subdominio' }}</div><small
                                        class="muted">{{ data_get($check->results, 'title', 'Sin title') }} ·
                                        {{ data_get($check->results, 'meta_description') ? 'description OK' : 'sin description' }}</small>
                                </div>
                                <span
                                    class="{{ data_get($check->results, 'error') ? 'status--danger' : 'status--ok' }}">{{ data_get($check->results, 'error') ? 'Error' : data_get($check->results, 'status_code', 'OK') }}</span>
                        </div>@empty<p class="muted">A la espera del primer snapshot. Ejecuta <code>php8.3 artisan
                                    monitor:seo</code>.</p>
                        @endforelse
                    </div>
                </section>
            @else
                <section class="panel" style="margin-top: 2.5rem">
                    <h3>Ejecuciones recientes</h3>
                    <div class="data-list">
                        @forelse ($executions as $execution)
                            <div class="data-list__row">
                                <div>
                                    <div>{{ $execution->workflow_name ?? 'Ejecucion #' . $execution->execution_id }}</div>
                                    <small
                                        class="muted">{{ $execution->started_at?->locale('es')->isoFormat('L LTS') ?? 'Fecha no disponible' }}</small>
                                </div><span
                                    class="{{ $execution->status === 'success' ? 'status--ok' : 'status--danger' }}">{{ $execution->status ?? 'Sin estado' }}</span>
                        </div>@empty<p class="muted">A la espera del primer snapshot.</p>
                        @endforelse
                    </div>
                </section>
            @endif
        </main>
    </div>
@endsection

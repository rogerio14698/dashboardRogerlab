@extends('layouts.app')

@section('content')
    <div class="page">
        @include('partials.header')
        <main class="page__content">
            <div class="dashboard__intro">
                <p class="eyebrow">Backup</p>
                <h2>Copia de seguridad del servidor</h2>
                <p class="status--ok">{{ $serverIp ?? 'Servidor' }} · disponible</p>
                <p class="muted">Ultima actualizacion:
                    {{ \Illuminate\Support\Carbon::parse($updatedAt)->locale('es')->isoFormat('L LTS') }}</p>
            </div>
            @if ($errors->has('backup'))
                <div class="notice notice--danger" role="alert">{{ $errors->first('backup') }}</div>
            @endif

            <div class="backup-layout">
                <section class="panel">
                    <div class="panel__heading">
                        <div>
                            <p class="eyebrow">Generar copia</p>
                            <h3>Selecciona los datos importantes</h3>
                        </div>
                        <span class="status--ok">ZIP descargable</span>
                    </div>
                    <p class="muted">Se excluyen logs, caches temporales y capas efimeras de Docker. No se respalda
                        <code>/var</code> completo.</p>

                    <form method="POST" action="{{ route('backup.create') }}" class="backup-form">
                        @csrf
                        <div class="backup-sources">
                            @foreach ($sources as $key => $source)
                                <label class="backup-source">
                                    <input type="checkbox" name="sources[]" value="{{ $key }}"
                                        @checked(old('sources.' . $loop->index, in_array($key, ['web', 'mysql', 'docker', 'ssl', 'cron', 'cron_system', 'cron_users', 'passwd', 'env'], true)))>
                                    <span>
                                        <strong>{{ $source['label'] }}</strong>
                                        <small>{{ $source['description'] }}</small>
                                        <code>{{ $source['path'] ?: 'Configura BACKUP_SSH_PATH' }}</code>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('sources')
                            <p class="status--danger">{{ $message }}</p>
                        @enderror
                        <button class="button button--primary" type="submit">Generar y descargar backup</button>
                    </form>
                </section>

                <aside class="backup-sidebar">
                    <section class="card">
                        <span class="stat__label">Ultimo backup local</span>
                        @if ($latestBackup)
                            <div class="stat__value">{{ $latestBackup['size'] }}</div>
                            <p class="muted">{{ $latestBackup['name'] }}</p>
                            <a class="button" href="{{ route('backup.download', $latestBackup['name']) }}">Descargar de nuevo</a>
                        @else
                            <div class="stat__value">Ninguno</div>
                            <p class="muted">La primera copia aparecera aqui.</p>
                        @endif
                    </section>
                    <section class="card">
                        <span class="stat__label">Google Drive</span>
                        <div class="stat__value">{{ $driveReady ? 'Preparado' : 'Pendiente' }}</div>
                        <p class="muted">{{ $driveReady ? 'Las credenciales de subida estan configuradas.' : 'Falta configurar un token de refresco y una carpeta destino.' }}</p>
                        <small class="muted">El Client ID y el Secret OAuth no son un API key ni autorizan por si solos una subida.</small>
                    </section>
                </aside>
            </div>

            <section class="panel backup-notes">
                <h3>Politica de respaldo</h3>
                <div class="data-list">
                    <div class="data-list__row"><span>Incluido</span><span class="muted">Web, MySQL, Docker volumes, SSL, cron, usuarios, SSH y .env</span></div>
                    <div class="data-list__row"><span>Excluido</span><span class="muted">/var/log, /var/cache, /var/tmp, overlay2 y contenedores efimeros</span></div>
                    <div class="data-list__row"><span>Destino actual</span><span class="status--ok">Ordenador mediante descarga</span></div>
                    <div class="data-list__row"><span>Destino futuro</span><span class="muted">Google Drive con OAuth autorizado</span></div>
                </div>
            </section>
        </main>
    </div>
@endsection

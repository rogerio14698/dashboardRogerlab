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
            @if ($errors->has('upload'))
                <div class="notice notice--danger" role="alert">{{ $errors->first('upload') }}</div>
            @endif
            @if (session('backup_queued'))
                <div class="notice notice--success" role="status">
                    Backup enviado a la cola. El enlace de descarga aparecera cuando termine el worker.
                </div>
            @endif
            @if (session('backup_uploaded'))
                <div class="notice notice--success" role="status">Punto de retorno subido correctamente.</div>
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
                        <label class="field backup-name">
                            Nombre del backup (opcional)
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="rogerlab-produccion">
                            <small class="muted">Sin nombre se usa rogerlab-fecha-peso.zip.</small>
                        </label>
                        <div class="backup-sources">
                            @foreach ($sources as $key => $source)
                                <label class="backup-source">
                                    <input type="checkbox" name="sources[]" value="{{ $key }}"
                                        @checked(in_array($key, old('sources', $source['available'] && in_array($key, ['web', 'mysql', 'docker', 'ssl', 'cron', 'cron_system', 'cron_users', 'passwd', 'env'], true) ? [$key] : []), true))>
                                    <span>
                                        <strong>{{ $source['label'] }}</strong>
                                        <small>{{ $source['description'] }}</small>
                                        <code>{{ $source['path'] ?: 'Configura BACKUP_SSH_PATH' }}</code>
                                    </span>
                                </label>
                            @endforeach
                            @foreach ($projectEnvSources as $key => $source)
                                <label class="backup-source">
                                    <input type="checkbox" name="sources[]" value="{{ $key }}"
                                        @checked(in_array($key, old('sources', []), true))>
                                    <span>
                                        <strong>{{ $source['label'] }}</strong>
                                        <small>{{ $source['description'] }}</small>
                                        <code>{{ $source['path'] }}</code>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('sources')
                            <p class="status--danger">{{ $message }}</p>
                        @enderror
                        <button class="button button--primary" type="submit">Generar backup</button>
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

            <section class="panel backup-upload">
                <div class="panel__heading">
                    <div>
                        <p class="eyebrow">Punto de retorno</p>
                        <h3>Subir un backup existente</h3>
                    </div>
                    <span class="status--ok">ZIP hasta 4 GB</span>
                </div>
                <p class="muted">El archivo se guarda en el servidor para poder descargarlo o usarlo en una restauracion posterior. No se extrae automaticamente.</p>
                <p>La ruta de guardado en el servidor es: <code>{{ storage_path('app/backups') }}</code></p>
                
                <form method="POST" action="{{ route('backup.upload') }}" enctype="multipart/form-data" class="backup-upload__form">
                    @csrf
                    <input type="file" name="backup_file" accept=".zip,application/zip" required>
                    <button class="button" type="submit">Subir punto de retorno</button>
                </form>
            </section>
        </main>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="pagina">
        @include('partials.header')
        <main class="paginaContenido">
            <div class="introduccionPanel">
                <p class="etiquetaSuperior">Backup</p>
                <h2>Copia de seguridad del servidor</h2>
                <p class="estadoCorrecto">{{ $serverIp ?? 'Servidor' }} · disponible</p>
                <p class="textoSuave">Ultima actualizacion:
                    {{ \Illuminate\Support\Carbon::parse($updatedAt)->locale('es')->isoFormat('L LTS') }}</p>
            </div>
            @if ($errors->has('backup'))
                <div class="aviso avisoPeligro" role="alert">{{ $errors->first('backup') }}</div>
            @endif
            @if ($errors->has('upload'))
                <div class="aviso avisoPeligro" role="alert">{{ $errors->first('upload') }}</div>
            @endif
            @if (session('backup_queued'))
                <div class="aviso avisoCorrecto" role="status">
                    Backup enviado a la cola. El enlace de descarga aparecera cuando termine el worker.
                </div>
            @endif
            @if (session('backup_uploaded'))
                <div class="aviso avisoCorrecto" role="status">Punto de retorno subido correctamente.</div>
            @endif

            <div class="rejillaCopias">
                <section class="panel">
                    <div class="encabezadoPanel">
                        <div>
                            <p class="etiquetaSuperior">Generar copia</p>
                            <h3>Selecciona los datos importantes</h3>
                        </div>
                        <span class="estadoCorrecto">ZIP descargable</span>
                    </div>
                    <p class="textoSuave">Se excluyen logs, caches temporales y capas efimeras de Docker. No se respalda
                        <code>/var</code> completo.</p>

                    <form method="POST" action="{{ route('backup.create') }}" class="formularioCopia">
                        @csrf
                        <label class="campo nombreCopia">
                            Nombre del backup (opcional)
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="rogerlab-produccion">
                            <small class="textoSuave">Sin nombre se usa rogerlab-fecha-peso.zip.</small>
                        </label>
                        <div class="fuentesCopia">
                            @foreach ($sources as $key => $source)
                                <label class="fuenteCopia">
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
                                <label class="fuenteCopia">
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
                            <p class="estadoPeligro">{{ $message }}</p>
                        @enderror
                        <button class="boton botonPrincipal" type="submit">Generar backup</button>
                    </form>
                </section>

                <aside class="barraLateralCopias">
                    <section class="tarjeta">
                        <span class="etiquetaEstadistica">Ultimo backup local</span>
                        @if ($latestBackup)
                            <div class="valorEstadistica">{{ $latestBackup['size'] }}</div>
                            <p class="textoSuave">{{ $latestBackup['name'] }}</p>
                            <a class="boton" href="{{ route('backup.download', $latestBackup['name']) }}">Descargar de nuevo</a>
                        @else
                            <div class="valorEstadistica">Ninguno</div>
                            <p class="textoSuave">La primera copia aparecera aqui.</p>
                        @endif
                    </section>
                    <section class="tarjeta">
                        <span class="etiquetaEstadistica">Google Drive</span>
                        <div class="valorEstadistica">{{ $driveReady ? 'Preparado' : 'Pendiente' }}</div>
                        <p class="textoSuave">{{ $driveReady ? 'Las credenciales de subida estan configuradas.' : 'Falta configurar un token de refresco y una carpeta destino.' }}</p>
                        <small class="textoSuave">El Client ID y el Secret OAuth no son un API key ni autorizan por si solos una subida.</small>
                    </section>
                </aside>
            </div>

            <section class="panel notasCopia">
                <h3>Politica de respaldo</h3>
                <div class="listaDatos">
                    <div class="filaDatos"><span>Incluido</span><span class="textoSuave">Web, MySQL, Docker volumes, SSL, cron, usuarios, SSH y .env</span></div>
                    <div class="filaDatos"><span>Excluido</span><span class="textoSuave">/var/log, /var/cache, /var/tmp, overlay2 y contenedores efimeros</span></div>
                    <div class="filaDatos"><span>Destino actual</span><span class="estadoCorrecto">Ordenador mediante descarga</span></div>
                    <div class="filaDatos"><span>Destino futuro</span><span class="textoSuave">Google Drive con OAuth autorizado</span></div>
                </div>
            </section>

            <section class="panel subidaCopia">
                <div class="encabezadoPanel">
                    <div>
                        <p class="etiquetaSuperior">Punto de retorno</p>
                        <h3>Subir un backup existente</h3>
                    </div>
                    <span class="estadoCorrecto">ZIP hasta 4 GB</span>
                </div>
                <p class="textoSuave">El archivo se guarda en el servidor para poder descargarlo o usarlo en una restauracion posterior. No se extrae automaticamente.</p>
                <p>La ruta de guardado en el servidor es: <code>{{ storage_path('app/backups') }}</code></p>
                
                <form method="POST" action="{{ route('backup.upload') }}" enctype="multipart/form-data" class="formularioSubidaCopia">
                    @csrf
                    <input type="file" name="backup_file" accept=".zip,application/zip" required>
                    <button class="boton" type="submit">Subir punto de retorno</button>
                </form>
            </section>
        </main>
    </div>
@endsection

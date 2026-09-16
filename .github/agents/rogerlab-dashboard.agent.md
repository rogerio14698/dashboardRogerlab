---
name: rogerlab-dashboard
description: "Agente para mantener el dashboard privado Rogerlab: Laravel 12, Blade, SCSS, TypeScript, monitorizacion, colas y backups del servidor. Usar al implementar, depurar o revisar modulos del dashboard, especialmente Backup, permisos, workers y despliegue Linux."
---

# Rogerlab Dashboard Agent

## Contexto del proyecto

- Aplicacion Laravel 12 para monitorizar el servidor Rogerlab.
- Frontend actual: Blade, SCSS y TypeScript con Vite. No asumir Inertia, React o Tailwind sin verificarlo.
- Modulos de dominio en `app/Domain`.
- Rutas web protegidas por `auth` en `routes/web.php`.
- Comandos y scheduler en `routes/console.php`.
- PHP requerido por `composer.json`: 8.3+.

## Comandos de validacion

Ejecutar desde la raiz del proyecto:

```bash
php artisan view:cache
php artisan route:list --path=backup
php artisan test
npm run build
```

Para probar el backup pequeno:

```bash
php artisan backup:run env
```

No ejecutar `backup:run --all` en una peticion web ni como prueba automatica sin confirmar el tamano de las fuentes.

## Backup

El flujo de Backup esta repartido entre:

- `app/Domain/Backup/BackupService.php`: fuentes, descubrimiento de `.env`, ZIP, nombres, uploads y descargas.
- `app/Jobs/CreateBackup.php`: generacion en cola fuera de HTTP.
- `app/Console/Commands/CreateBackup.php`: comando `backup:run`.
- `app/Http/Controllers/DashboardController.php`: vista, encolado, upload y descarga.
- `config/backup.php`: rutas, exclusiones, limite y Google Drive.
- `resources/views/dashboard/backup.blade.php`: interfaz del panel.

El POST del panel debe responder rapido y encolar `CreateBackup`. No mover la generacion pesada de vuelta al controlador web: puede matar PHP-FPM y provocar 502.

Los nombres sin personalizar siguen el formato `rogerlab-YYYYMMDD-HHmmss-pesoMB.zip`. Los nombres personalizados deben sanitizarse y nunca permitir rutas de filesystem.

Los `.env` de proyectos se descubren como directorios de primer nivel bajo `BACKUP_WEB_PATH`. Cada seleccion debe ser una clave generada por el servidor, nunca una ruta arbitraria recibida del navegador.

Los ZIP subidos son puntos de retorno. Validar extension y estructura con `ZipArchive`; no extraerlos automaticamente ni sobrescribir el servidor sin una futura operacion de restauracion explicita, autenticada y con confirmacion.

No respaldar `/var` completo. Mantener exclusiones de logs, caches, temporales, `overlay2` y contenedores efimeros. Para MySQL activo, preferir `mysqldump` sobre copiar directamente `/var/lib/mysql`.

## Seguridad

- No leer, imprimir ni copiar secretos desde `.env` a respuestas, logs, README o commits.
- Tratar cualquier secreto expuesto como comprometido y recomendar rotacion.
- No usar `chmod 777`.
- Verificar permisos del usuario de PHP-FPM y del worker sobre las fuentes y `storage/app/private/backups`.
- No aceptar paths arbitrarios, comandos shell del navegador ni nombres de ZIP con traversal.
- Google Drive requiere OAuth autorizado y `GOOGLE_DRIVE_REFRESH_TOKEN`; el folder ID y Client ID/Secret no bastan para subir archivos.

## Produccion Linux

Debe existir un worker persistente, gestionado por Supervisor o systemd:

```bash
php8.3 artisan queue:work database --queue=default --sleep=3 --tries=1 --timeout=3600
```

Despues de desplegar codigo:

```bash
php8.3 artisan optimize:clear
php8.3 artisan queue:restart
php8.3 artisan config:cache
```

Para investigar 502, revisar simultaneamente `storage/logs/laravel.log`, `journalctl -u php8.3-fpm -f`, `/var/log/nginx/error.log`, `free -h`, `df -h` y mensajes OOM de `dmesg`.

## Estilo de cambios

- Mantener cambios pequenos y locales.
- Seguir patrones existentes de Laravel y Blade.
- Preferir servicios de dominio para logica de filesystem.
- Añadir pruebas focalizadas para autorizacion, nombres, traversal, ZIP invalidos y colas cuando se modifique Backup.
- Validar despues de editar y no ignorar errores de compilacion o de rutas.

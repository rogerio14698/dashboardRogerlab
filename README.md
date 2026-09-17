# Rogerlab Server Watch

Dashboard privado de monitorizacion del servidor Rogerlab. La aplicacion esta construida con Laravel 12, Blade, SCSS, TypeScript y MariaDB/MySQL.
## Requisitos

- PHP 8.3 o superior.
- Composer.
- Node.js 20 o superior y npm.
- MySQL/MariaDB.
- Extensiones PHP `zip`, `pdo_mysql` y `openssl` para el modulo de Backup.

## Instalacion

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```
En Windows, usa `Copy-Item .env.example .env` en lugar de `cp`.

Configura en `.env` la base de datos, `ADMIN_PASSWORD`, el servidor monitorizado y las integraciones necesarias. Nunca subas `.env` al repositorio ni copies sus secretos en documentacion, logs o respuestas HTTP.
## Ejecucion

Para desarrollo:

```bash
php artisan serve
npm run dev
```
En produccion deben estar activos el scheduler y el worker de colas:

```bash
php artisan schedule:work
php artisan queue:work database --queue=default --sleep=3 --tries=1 --timeout=3600
```
El worker es necesario para generar Backups. Si no esta activo, el panel mostrara que el trabajo fue enviado a la cola, pero no se generara ningun ZIP.

## Areas del dashboard

- **Inicio**: resumen y acceso a los modulos.
- **Metricas**: carga, memoria, contenedores, uptime, n8n, SEO y alertas.
- **Domains**: dominios y subdominios monitorizados.
- **Database**: explorador y operaciones CRUD autorizadas.
- **Gestor de contenido**: selector de web para conectar dinamicamente a la base de datos adecuada.
- **Servidores y Redes**: metricas del sistema, Docker y uptime.
- **Backup**: generacion, descarga, subida de puntos de retorno y seleccion de fuentes.

Todas las rutas privadas estan protegidas por autenticacion. El navegador no debe acceder directamente al socket Docker, al sistema operativo ni a secretos.

## Gestor de contenido multiweb

El gestor de contenido permite elegir una web desde un selector y conectar automaticamente a la base de datos asociada. La idea es simple y escalable: cada web tiene sus propias variables en `.env` y el controlador carga la configuracion correcta cuando se selecciona una opcion.

Ejemplo de configuracion:

```dotenv
WEB_DB_PORTFOLIO_DRIVER=mysql
WEB_DB_PORTFOLIO_HOST=127.0.0.1
WEB_DB_PORTFOLIO_PORT=3306
WEB_DB_PORTFOLIO_DATABASE=portfolio_blog
WEB_DB_PORTFOLIO_USERNAME=root
WEB_DB_PORTFOLIO_PASSWORD=

WEB_DB_WEB2_DRIVER=mysql
WEB_DB_WEB2_HOST=127.0.0.1
WEB_DB_WEB2_PORT=3306
WEB_DB_WEB2_DATABASE=web2_blog
WEB_DB_WEB2_USERNAME=root
WEB_DB_WEB2_PASSWORD=
```

Si quieres anadir otra web, solo duplicas ese bloque con otra clave como `WEB_DB_WEB3_...` y la agregas al selector del formulario. El flujo es:

1. El usuario elige `portfolio`, `web2`, `web3`.
2. Laravel lee la configuracion correspondiente desde `.env`.
3. La aplicacion contecta a esa base de datos y muestra sus tablas.
4. A partir de aqui ya puedes crear el CRUD para insertar, editar o borrar contenido de esa web concreta.

## Backup

La pantalla `/backup` permite:

1. Seleccionar fuentes del servidor.
2. Escribir un nombre personalizado, por ejemplo `rogerlab-produccion`.
3. Generar un ZIP en segundo plano.
4. Descargar el ZIP cuando termine el worker.
5. Subir un ZIP existente como punto de retorno.
6. Seleccionar individualmente los archivos `.env` encontrados en cada proyecto de `BACKUP_WEB_PATH`.

Sin nombre personalizado, el formato es:

```text
rogerlab-YYYYMMDD-HHmmss-pesoMB.zip
```
Las fuentes configurables son:

- Proyectos web: `BACKUP_WEB_PATH`.
- Datos de MySQL: `BACKUP_MYSQL_PATH`.
- Volumenes persistentes de Docker: `BACKUP_DOCKER_VOLUMES_PATH`.
- Certificados: `BACKUP_SSL_PATH`.
- Cron del sistema y de usuarios.
- Usuarios, `.ssh` y `.env` del dashboard.
- `.env` individual de cada proyecto encontrado.

No se incluye `/var` completo. Se excluyen logs, caches, temporales, capas `overlay2` y contenedores efimeros. Los datos de MySQL deberian respaldarse preferentemente con `mysqldump` antes que copiando directamente archivos activos de `/var/lib/mysql`.
### Comando manual

Para ejecutar un backup fuera del navegador:

```bash
php artisan backup:run env
php artisan backup:run env web
php artisan backup:run --all
```
### Permisos

El usuario de PHP-FPM/worker debe poder leer las fuentes seleccionadas y escribir en `storage/app/private/backups`. No uses `chmod 777`. Concede permisos mínimos mediante el usuario/grupo del servicio, ACL o un servicio privilegiado limitado.

### Diagnostico de un 502

Comprueba el worker:

```bash
ps aux | grep '[q]ueue:work'
php artisan tinker --execute="echo DB::table('jobs')->count() . PHP_EOL;"
```
Observa los logs durante una prueba:

```bash
tail -f storage/logs/laravel.log
journalctl -u php8.3-fpm -f
tail -f /var/log/nginx/error.log
```

Si aparece `Connection reset by peer`, revisa memoria y procesos terminados por el kernel:

```bash
free -h
df -h
dmesg -T | grep -Ei 'out of memory|oom|killed process'
```

El POST solo encola el trabajo; la generacion pesada no debe ejecutarse dentro de la peticion HTTP.
## Google Drive

El panel puede mostrar si Drive esta preparado mediante OAuth. El ID de carpeta no es una credencial y el Client ID/Secret no autorizan por si solos una subida. Para habilitar subidas se necesita un `refresh token` obtenido mediante el flujo OAuth correcto:

```dotenv
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_FOLDER_ID=
GOOGLE_DRIVE_REFRESH_TOKEN=
```

Despues de cambiar `.env` en produccion:

```bash
php artisan config:clear
php artisan config:cache
```

Rota cualquier secreto que haya sido expuesto y no lo incluyas en incidencias, capturas o commits.
## Arquitectura

La logica de cada modulo vive en `app/Domain`. Los comandos de monitorizacion se registran en `routes/console.php`. El flujo de Backup se reparte entre:

- `app/Domain/Backup/BackupService.php`: fuentes, ZIP, nombres, uploads y descargas.
- `app/Jobs/CreateBackup.php`: generacion fuera de la peticion web.
- `app/Console/Commands/CreateBackup.php`: ejecucion manual.
- `app/Http/Controllers/DashboardController.php`: panel y endpoints protegidos.
- `config/backup.php`: rutas, exclusiones, limites y Drive.

## Validacion

Antes de desplegar:

```bash
php artisan view:cache
php artisan route:list --path=backup
php artisan test
npm run build
```

El test de ejemplo de la aplicacion puede esperar `200` en `/`, aunque esa ruta redirige a login (`302`) cuando el usuario no esta autenticado; revisa esa expectativa si falla.
# Rogerlab Server Watch

Dashboard privado de monitorizacion para `152.228.234.57`, construido con Laravel 12, Inertia, React, TypeScript, Tailwind y MariaDB/MySQL.

## Arquitectura

Cada dominio vive bajo `app/Domain` y expone un contrato sustituible:

- `SystemMetrics`: lectura local de procfs y carga del host.
- `Docker`: cliente HTTP sobre `DOCKER_SOCKET`; solo permite `start`, `stop` y `restart`.
- `Uptime`: comprobacion HTTP preparada para una futura implementacion Cloudflare.
- `Seo`: robots, sitemap y metadatos basicos.
- `N8n`: cliente REST autenticado con `N8N_API_KEY`.
- `Alerts`: fingerprints, cooldown y notificacion en cola.

Los comandos `monitor:system`, `monitor:docker`, `monitor:uptime` y `monitor:n8n` se agendan en `routes/console.php`. El navegador nunca accede al sistema operativo, socket Docker ni secretos.

## Puesta en marcha

1. Usa PHP 8.3+, Node 20+ y MySQL/MariaDB. El PHP local 8.2 no cumple el requisito de despliegue.
2. Copia `.env.example` a `.env`, cambia `ADMIN_PASSWORD` y completa base de datos, correo y n8n.
3. Ejecuta `composer install`, `npm install`, `php artisan key:generate` y `php artisan migrate --seed`.
4. Mantén un worker con `php artisan queue:work` y el scheduler con `php artisan schedule:work`.
5. Compila con `npm run build`.

El registro publico esta desactivado. Fortify queda instalado con soporte de 2FA para activarlo desde una pantalla protegida cuando se implemente esa UX.rm -rf /var/www/dashboardRogerlab/storage/app/private/backups/*

## Añadir un modulo

Crea un contrato en `app/Domain/Modulo`, una implementacion, un modelo/migracion de snapshots, un comando Artisan y una pagina Inertia. Registra la implementacion en `AppServiceProvider` y agenda el comando en `routes/console.php`. Las alertas deben pasar por `AlertService` usando un fingerprint estable.

## Cloudflare y subdominios

Una futura implementacion `CloudflareUptimeChecker` puede usar la API para analiticas, proxy y DNS mientras el modelo `Subdomain` y el comando permanecen iguales. La clave debe vivir en `.env`, con policy y Form Request antes de exponer cualquier gestion DNS.

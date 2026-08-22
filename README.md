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

El registro publico esta desactivado. Fortify queda instalado con soporte de 2FA para activarlo desde una pantalla protegida cuando se implemente esa UX.

## Añadir un modulo

Crea un contrato en `app/Domain/Modulo`, una implementacion, un modelo/migracion de snapshots, un comando Artisan y una pagina Inertia. Registra la implementacion en `AppServiceProvider` y agenda el comando en `routes/console.php`. Las alertas deben pasar por `AlertService` usando un fingerprint estable.

## Cloudflare y subdominios

Una futura implementacion `CloudflareUptimeChecker` puede usar la API para analiticas, proxy y DNS mientras el modelo `Subdomain` y el comando permanecen iguales. La clave debe vivir en `.env`, con policy y Form Request antes de exponer cualquier gestion DNS.

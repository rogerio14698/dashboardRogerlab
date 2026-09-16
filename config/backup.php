<?php

return [
    'sources' => [
        'web' => [
            'label' => 'Proyectos web',
            'description' => 'Aplicaciones y sitios publicados.',
            'path' => env('BACKUP_WEB_PATH', '/var/www'),
            'type' => 'directory',
        ],
        'mysql' => [
            'label' => 'Datos de MySQL',
            'description' => 'Archivos persistentes de MySQL.',
            'path' => env('BACKUP_MYSQL_PATH', '/var/lib/mysql'),
            'type' => 'directory',
        ],
        'docker' => [
            'label' => 'Volúmenes de Docker',
            'description' => 'Datos persistentes de contenedores.',
            'path' => env('BACKUP_DOCKER_VOLUMES_PATH', '/var/lib/docker/volumes'),
            'type' => 'directory',
        ],
        'ssl' => [
            'label' => 'Certificados SSL',
            'description' => 'Certificados y configuración de Certbot.',
            'path' => env('BACKUP_SSL_PATH', '/etc/letsencrypt'),
            'type' => 'directory',
        ],
        'cron' => [
            'label' => 'Tareas programadas',
            'description' => 'Crontab del sistema y tareas de cron.',
            'path' => env('BACKUP_CRON_PATH', '/etc/cron.d'),
            'type' => 'directory',
        ],
        'cron_system' => [
            'label' => 'Crontab del sistema',
            'description' => 'Configuracion global de /etc/crontab.',
            'path' => env('BACKUP_CRONTAB_PATH', '/etc/crontab'),
            'type' => 'file',
        ],
        'cron_users' => [
            'label' => 'Crontabs de usuarios',
            'description' => 'Tareas creadas con crontab -e.',
            'path' => env('BACKUP_USER_CRONTABS_PATH', '/var/spool/cron'),
            'type' => 'directory',
        ],
        'passwd' => [
            'label' => 'Usuarios del sistema',
            'description' => 'Identidades y permisos básicos del servidor.',
            'path' => env('BACKUP_PASSWD_PATH', '/etc/passwd'),
            'type' => 'file',
        ],
        'ssh' => [
            'label' => 'Claves SSH',
            'description' => 'Clave SSH del usuario configurado.',
            'path' => env('BACKUP_SSH_PATH'),
            'type' => 'directory',
        ],
        'env' => [
            'label' => 'Variables de entorno',
            'description' => 'Configuración sensible de esta aplicación.',
            'path' => base_path('.env'),
            'type' => 'file',
        ],
    ],
    'exclude' => [
        '/var/log',
        '/var/cache',
        '/var/tmp',
        '/var/lib/docker/overlay2',
        '/var/lib/docker/containers',
    ],
    'storage_path' => storage_path('app/private/backups'),
    'max_size_mb' => (int) env('BACKUP_MAX_SIZE_MB', 4096),
    'google_drive' => [
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID', env('ID_CLIENTE_GOOGLECLOUD')),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET', env('SECRET_CLIENTE_GOOGLECLOUD')),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
    ],
];

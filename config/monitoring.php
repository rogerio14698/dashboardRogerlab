<?php

return [
    'server_ip' => env('SERVER_IP', '152.228.234.57'),
    'admin_name' => env('ADMIN_NAME', 'Roger Lucas'),
    'admin_email' => env('ADMIN_EMAIL', 'rogerlucas@rogerlab.es'),
    'admin_password' => env('ADMIN_PASSWORD'),
    'subdomains' => env('MONITORED_SUBDOMAINS', ''),
    'alert_cooldown_minutes' => env('ALERT_COOLDOWN_MINUTES', 30),
    'ram_threshold' => env('ALERT_RAM_THRESHOLD', 90),
    'disk_threshold' => env('ALERT_DISK_THRESHOLD', 85),
];

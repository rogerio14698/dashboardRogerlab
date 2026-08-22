<?php

return [
    'admin_email' => env('ADMIN_EMAIL', 'rogerlucas@rogerlab.es'),
    'alert_cooldown_minutes' => env('ALERT_COOLDOWN_MINUTES', 30),
    'ram_threshold' => env('ALERT_RAM_THRESHOLD', 90),
    'disk_threshold' => env('ALERT_DISK_THRESHOLD', 85),
];

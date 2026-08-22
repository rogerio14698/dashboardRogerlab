<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('monitor:system')->everyMinute()->withoutOverlapping();
Schedule::command('monitor:uptime')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('monitor:seo')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('monitor:docker')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('monitor:n8n')->everyTenMinutes()->withoutOverlapping();

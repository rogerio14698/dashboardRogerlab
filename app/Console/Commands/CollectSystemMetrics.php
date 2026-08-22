<?php

namespace App\Console\Commands;

use App\Domain\SystemMetrics\SystemMetricsCollectorInterface;
use App\Models\SystemMetric;
use Illuminate\Console\Command;

class CollectSystemMetrics extends Command
{
    protected $signature = 'monitor:system';
    protected $description = 'Capture a host metrics snapshot.';

    public function handle(SystemMetricsCollectorInterface $collector): int
    {
        SystemMetric::create(['snapshot' => $collector->collect(), 'captured_at' => now()]);
        return self::SUCCESS;
    }
}

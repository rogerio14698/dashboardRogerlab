<?php

namespace App\Domain\SystemMetrics;

interface SystemMetricsCollectorInterface
{
    /** @return array<string, mixed> */
    public function collect(): array;
}

<?php

namespace App\Domain\SystemMetrics;

class SystemMetricsCollector implements SystemMetricsCollectorInterface
{
    public function collect(): array
    {
        $load = function_exists('sys_getloadavg') ? \sys_getloadavg() : [null, null, null];
        $memory = $this->readMemory();

        return [
            'cpu_usage' => null,
            'load_1m' => $load[0] ?? null,
            'load_5m' => $load[1] ?? null,
            'load_15m' => $load[2] ?? null,
            'memory' => $memory,
            'disks' => [],
            'network' => [],
        ];
    }

    /** Linux procfs is intentionally isolated here so tests can replace this adapter. */
    private function readMemory(): array
    {
        $contents = @file_get_contents('/proc/meminfo');
        if ($contents === false) {
            return [];
        }

        preg_match_all('/^(MemTotal|MemAvailable):\s+(\d+)\s+kB$/m', $contents, $matches);
        $values = array_combine($matches[1] ?? [], array_map('intval', $matches[2] ?? []));

        return [
            'total_kb' => $values['MemTotal'] ?? null,
            'available_kb' => $values['MemAvailable'] ?? null,
        ];
    }
}

<?php

namespace App\Domain\Uptime;

use Illuminate\Support\Facades\Http;
use Throwable;

class HttpUptimeChecker implements UptimeCheckerInterface
{
    public function check(string $url): array
    {
        $started = microtime(true);
        try {
            $response = Http::timeout(10)->withHeaders(['User-Agent' => 'RogerlabServerWatch/1.0'])->get($url);

            return [
                'status_code' => $response->status(),
                'available' => $response->successful(),
                'response_time_ms' => round((microtime(true) - $started) * 1000, 2),
                'error' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'status_code' => null,
                'available' => false,
                'response_time_ms' => round((microtime(true) - $started) * 1000, 2),
                'error' => $exception->getMessage(),
            ];
        }
    }
}

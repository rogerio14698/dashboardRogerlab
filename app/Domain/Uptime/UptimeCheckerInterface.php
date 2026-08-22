<?php

namespace App\Domain\Uptime;

interface UptimeCheckerInterface
{
    /** @return array<string, mixed> */
    public function check(string $url): array;
}

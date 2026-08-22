<?php

namespace App\Domain\N8n;

interface N8nClientInterface
{
    /** @return array<int, array<string, mixed>> */
    public function recentExecutions(int $limit = 50): array;
}

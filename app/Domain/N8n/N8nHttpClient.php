<?php

namespace App\Domain\N8n;

use Illuminate\Support\Facades\Http;

class N8nHttpClient implements N8nClientInterface
{
    public function recentExecutions(int $limit = 50): array
    {
        return Http::withHeaders(['X-N8N-API-KEY' => config('services.n8n.api_key')])
            ->timeout(10)
            ->get(rtrim(config('services.n8n.url'), '/') . '/api/v1/executions', ['limit' => min(100, $limit)])
            ->throw()
            ->json('data', []);
    }
}

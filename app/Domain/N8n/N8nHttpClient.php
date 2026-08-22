<?php

namespace App\Domain\N8n;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class N8nHttpClient implements N8nClientInterface
{
    public function recentExecutions(int $limit = 50): array
    {
        $apiKey = config('services.n8n.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('N8N_API_KEY no esta configurada en la configuracion de Laravel.');
        }

        return Http::withHeaders(['X-N8N-API-KEY' => $apiKey])
            ->timeout(10)
            ->get(rtrim(config('services.n8n.url'), '/') . '/api/v1/executions', ['limit' => min(100, $limit)])
            ->throw()
            ->json('data', []);
    }
}

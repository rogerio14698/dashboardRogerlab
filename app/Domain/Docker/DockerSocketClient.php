<?php

namespace App\Domain\Docker;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DockerSocketClient implements DockerClientInterface
{
    public function containers(): array
    {
        return $this->request('GET', '/containers/json?all=1');
    }

    public function action(string $containerId, string $action): void
    {
        if (! in_array($action, ['start', 'stop', 'restart'], true)) {
            throw new RuntimeException('Docker action is not whitelisted.');
        }

        $this->request('POST', "/containers/{$containerId}/{$action}");
    }

    public function logs(string $containerId, int $tail = 100): string
    {
        return (string) $this->request('GET', "/containers/{$containerId}/logs?stdout=1&stderr=1&tail=" . max(1, min($tail, 1000)));
    }

    private function request(string $method, string $uri): mixed
    {
        $response = Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => config('services.docker.socket')]])
            ->send($method, 'http://localhost' . $uri);

        if ($response->failed()) {
            throw new RuntimeException('Docker Engine API request failed.');
        }

        return $response->json() ?? $response->body();
    }
}

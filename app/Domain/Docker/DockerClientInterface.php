<?php

namespace App\Domain\Docker;

interface DockerClientInterface
{
    /** @return array<int, array<string, mixed>> */
    public function containers(): array;

    public function action(string $containerId, string $action): void;

    public function logs(string $containerId, int $tail = 100): string;
}

<?php

namespace App\Console\Commands;

use App\Domain\Docker\DockerClientInterface;
use App\Models\DockerContainer;
use Illuminate\Console\Command;

class CollectDockerMetrics extends Command
{
    protected $signature = 'monitor:docker';
    protected $description = 'Capture Docker container snapshots.';

    public function handle(DockerClientInterface $docker): int
    {
        foreach ($docker->containers() as $container) {
            DockerContainer::create([
                'container_id' => $container['Id'] ?? 'unknown',
                'name' => ltrim($container['Names'][0] ?? 'unknown', '/'),
                'image' => $container['Image'] ?? null,
                'status' => $container['Status'] ?? 'unknown',
                'snapshot' => $container,
                'captured_at' => now(),
            ]);
        }
        return self::SUCCESS;
    }
}

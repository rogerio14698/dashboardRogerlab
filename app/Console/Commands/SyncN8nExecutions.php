<?php

namespace App\Console\Commands;

use App\Domain\N8n\N8nClientInterface;
use App\Models\N8nExecution;
use Illuminate\Console\Command;
use Throwable;

class SyncN8nExecutions extends Command
{
    protected $signature = 'monitor:n8n';
    protected $description = 'Import recent n8n executions for alert evaluation.';

    public function handle(N8nClientInterface $client): int
    {
        try {
            foreach ($client->recentExecutions() as $execution) {
                N8nExecution::updateOrCreate(
                    ['execution_id' => (string) ($execution['id'] ?? '')],
                    ['workflow_name' => data_get($execution, 'workflowData.name'), 'status' => $execution['status'] ?? null, 'error' => data_get($execution, 'data.resultData.error.message'), 'payload' => $execution, 'started_at' => $execution['startedAt'] ?? null]
                );
            }
        } catch (Throwable $exception) {
            report($exception);
            $this->error('No se pudo consultar n8n: ' . $exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

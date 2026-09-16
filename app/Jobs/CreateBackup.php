<?php

namespace App\Jobs;

use App\Domain\Backup\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CreateBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(public array $sourceKeys)
    {
    }

    public function handle(BackupService $backupService): void
    {
        $backupService->create($this->sourceKeys);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
    }
}

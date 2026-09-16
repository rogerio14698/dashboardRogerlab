<?php

namespace App\Console\Commands;

use App\Domain\Backup\BackupService;
use Illuminate\Console\Command;
use Throwable;

class CreateBackup extends Command
{
    protected $signature = 'backup:run {sources?* : Source keys to include} {--all : Include every available source}';

    protected $description = 'Create a server backup ZIP outside the web request';

    public function handle(BackupService $backupService): int
    {
        $sources = $backupService->sources();
        $requested = $this->option('all')
            ? array_keys(array_filter($sources, static fn (array $source): bool => $source['available']))
            : $this->argument('sources');

        if ($requested === []) {
            $this->error('Indica fuentes, por ejemplo: php artisan backup:run env web');
            $this->line('Fuentes disponibles: ' . implode(', ', array_keys($sources)));

            return self::INVALID;
        }

        try {
            $path = $backupService->create($requested);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Backup creado: ' . $path);

        return self::SUCCESS;
    }
}

<?php

namespace App\Domain\Backup;

use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class BackupService
{
    public function sources(): array
    {
        return collect(config('backup.sources', []))
            ->map(function (array $source): array {
                $path = $source['path'];

                return $source + [
                    'available' => is_string($path) && $path !== '' && file_exists($path),
                ];
            })
            ->all();
    }

    public function latest(): ?array
    {
        $files = File::glob($this->storagePath() . DIRECTORY_SEPARATOR . '*.zip');

        if ($files === []) {
            return null;
        }

        usort($files, static fn (string $left, string $right): int => filemtime($right) <=> filemtime($left));
        $file = $files[0];

        return [
            'name' => basename($file),
            'size' => $this->formatBytes(filesize($file)),
            'created_at' => now()->setTimestamp(filemtime($file)),
        ];
    }

    public function create(array $sourceKeys): string
    {
        $selected = array_values(array_unique(array_filter($sourceKeys, 'is_string')));
        $sources = $this->sources();
        $unknown = array_diff($selected, array_keys($sources));

        if ($selected === [] || $unknown !== []) {
            throw new RuntimeException('Selecciona al menos una fuente de backup valida.');
        }

        File::ensureDirectoryExists($this->storagePath());
        $path = $this->storagePath() . DIRECTORY_SEPARATOR . 'backup-' . now()->format('Ymd-His') . '.zip';
        $archive = new ZipArchive();

        if ($archive->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo de backup.');
        }

        $manifest = [
            'created_at' => now()->toIso8601String(),
            'sources' => [],
            'excluded' => config('backup.exclude', []),
        ];

        foreach ($selected as $key) {
            $source = $sources[$key];
            $sourcePath = $source['path'];

            if (!is_string($sourcePath) || $sourcePath === '' || !file_exists($sourcePath)) {
                $manifest['sources'][$key] = ['path' => $sourcePath, 'status' => 'unavailable'];
                continue;
            }

            $manifest['sources'][$key] = ['path' => $sourcePath, 'status' => 'included'];
            $this->addPath($archive, $sourcePath, 'sources/' . $key, $source['type']);
        }

        $archive->addFromString('backup-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $archive->close();

        if (filesize($path) > config('backup.max_size_mb', 4096) * 1024 * 1024) {
            File::delete($path);
            throw new RuntimeException('El backup supera el limite configurado.');
        }

        return $path;
    }

    public function delete(string $path): void
    {
        if (is_file($path)) {
            File::delete($path);
        }
    }

    public function pathForDownload(string $name): string
    {
        $safeName = basename($name);
        $path = $this->storagePath() . DIRECTORY_SEPARATOR . $safeName;

        if ($safeName !== $name || !str_ends_with($safeName, '.zip') || !is_file($path)) {
            throw new RuntimeException('El backup solicitado no existe.');
        }

        return $path;
    }

    private function addPath(ZipArchive $archive, string $path, string $prefix, string $type): void
    {
        if ($type === 'file') {
            $archive->addFile($path, $prefix . '/' . basename($path));
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $this->isExcluded($file->getPathname())) {
                continue;
            }

            $relative = ltrim(str_replace($path, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $archive->addFile($file->getPathname(), $prefix . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relative));
        }
    }

    private function isExcluded(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);

        foreach (config('backup.exclude', []) as $excluded) {
            $excluded = rtrim(str_replace('\\', '/', $excluded), '/');

            if ($normalized === $excluded || str_starts_with($normalized, $excluded . '/')) {
                return true;
            }
        }

        return false;
    }

    private function storagePath(): string
    {
        return config('backup.storage_path', storage_path('app/private/backups'));
    }

    private function formatBytes(int|false $bytes): string
    {
        if ($bytes === false) {
            return 'Desconocido';
        }

        return match (true) {
            $bytes >= 1024 ** 3 => number_format($bytes / (1024 ** 3), 2) . ' GB',
            $bytes >= 1024 ** 2 => number_format($bytes / (1024 ** 2), 2) . ' MB',
            $bytes >= 1024 => number_format($bytes / 1024, 2) . ' KB',
            default => $bytes . ' B',
        };
    }
}

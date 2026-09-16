<?php

namespace App\Domain\Backup;

use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
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

    public function projectEnvSources(): array
    {
        $root = config('backup.projects_path');

        if (!is_dir($root)) {
            return [];
        }

        $projects = [];
        foreach (File::directories($root) as $projectPath) {
            $envPath = $projectPath . DIRECTORY_SEPARATOR . '.env';

            if (!is_file($envPath)) {
                continue;
            }

            $project = basename($projectPath);
            $key = 'project_env:' . $project;
            $projects[$key] = [
                'label' => $project . ' · .env',
                'description' => 'Variables de entorno del proyecto.',
                'path' => $envPath,
                'type' => 'file',
                'available' => true,
            ];
        }

        ksort($projects);

        return $projects;
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

    public function create(array $sourceKeys, ?string $requestedName = null): string
    {
        $selected = array_values(array_unique(array_filter($sourceKeys, 'is_string')));
        $sources = $this->sources() + $this->projectEnvSources();
        $unknown = array_diff($selected, array_keys($sources));

        if ($selected === [] || $unknown !== []) {
            throw new RuntimeException('Selecciona al menos una fuente de backup valida.');
        }

        File::ensureDirectoryExists($this->storagePath());
        $path = $this->storagePath() . DIRECTORY_SEPARATOR . 'building-' . bin2hex(random_bytes(8)) . '.zip';
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

        $size = filesize($path);
        $finalName = $this->safeArchiveName($requestedName, $size === false ? 0 : $size, $path);
        $finalPath = $this->storagePath() . DIRECTORY_SEPARATOR . $finalName;

        if ($finalPath !== $path) {
            File::move($path, $finalPath);
        }

        return $finalPath;
    }

    public function storeUpload(UploadedFile $upload): string
    {
        if (strtolower($upload->getClientOriginalExtension()) !== 'zip') {
            throw new RuntimeException('Solo se aceptan archivos ZIP validos.');
        }

        $archive = new ZipArchive();
        if ($archive->open($upload->getRealPath()) !== true) {
            throw new RuntimeException('El archivo ZIP no se puede abrir.');
        }
        $archive->close();

        File::ensureDirectoryExists($this->storagePath());
        $name = 'restore-point-' . now()->format('Ymd-His') . '-' . $upload->hashName();

        return $upload->move($this->storagePath(), $name)->getPathname();
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

    private function safeArchiveName(?string $requestedName, int $size = 0, ?string $currentPath = null): string
    {
        $base = $requestedName ? preg_replace('/[^a-zA-Z0-9_-]+/', '-', trim($requestedName)) : 'rogerlab';
        $base = trim((string) $base, '-_') ?: 'rogerlab';
        $suffix = $requestedName ? '' : '-' . now()->format('Ymd-His') . '-' . max(1, (int) ceil($size / 1024 / 1024)) . 'MB';
        $name = $base . $suffix . '.zip';
        $path = $this->storagePath() . DIRECTORY_SEPARATOR . $name;

        if ($currentPath !== null && $path === $currentPath) {
            return $name;
        }

        if (is_file($path)) {
            $name = $base . '-' . now()->format('Ymd-His') . '-' . bin2hex(random_bytes(2)) . $suffix . '.zip';
        }

        return $name;
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

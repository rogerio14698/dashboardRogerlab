<?php

namespace App\Http\Controllers;

use App\Domain\Backup\BackupService;
use App\Jobs\CreateBackup;
use App\Models\Alert;
use App\Models\DockerContainer;
use App\Models\N8nExecution;
use App\Models\SeoCheck;
use App\Models\SubdomainCheck;
use App\Models\SystemMetric;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    public function backup(BackupService $backupService): View
    {
        return view('dashboard.backup', [
            'serverIp' => config('monitoring.server_ip'),
            'updatedAt' => now()->toIso8601String(),
            'sources' => $backupService->sources(),
            'projectEnvSources' => $backupService->projectEnvSources(),
            'latestBackup' => $backupService->latest(),
            'driveReady' => filled(config('backup.google_drive.refresh_token')) && filled(config('backup.google_drive.folder_id')),
        ]);
    }

    public function createBackup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sources' => ['required', 'array', 'min:1'],
            'sources.*' => ['string', 'distinct'],
            'name' => ['nullable', 'string', 'max:80', 'regex:/^[a-zA-Z0-9 _-]+$/'],
        ]);

        CreateBackup::dispatch($validated['sources'], $validated['name'] ?? null);

        return redirect()->route('backup')->with('backup_queued', true);
    }

    public function uploadBackup(Request $request, BackupService $backupService): RedirectResponse
    {
        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'max:4194304'],
        ]);

        try {
            $backupService->storeUpload($validated['backup_file']);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['upload' => 'El archivo no es un ZIP valido.']);
        }

        return redirect()->route('backup')->with('backup_uploaded', true);
    }

    public function downloadBackup(string $backup, BackupService $backupService): BinaryFileResponse
    {
        return response()->download($backupService->pathForDownload($backup), $backup, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function __invoke(): View
    {
        $latestUptime = SubdomainCheck::query()
            ->with('subdomain:id,name,url')
            ->latest('checked_at')
            ->get()
            ->unique('subdomain_id')
            ->values();

        return view('dashboard.metrics', [
            
            'serverIp' => config('monitoring.server_ip'),
            'metrics' => SystemMetric::query()->latest('captured_at')->first(),
            'containers' => DockerContainer::query()->latest('captured_at')->limit(20)->get(),
            'uptime' => $latestUptime,
            'executions' => N8nExecution::query()->latest('started_at')->limit(10)->get(),
            'seo' => SeoCheck::query()->with('subdomain:id,name')->latest('checked_at')->limit(10)->get(),
            'alerts' => Alert::query()->whereNull('resolved_at')->latest('triggered_at')->limit(10)->get(),
            'updatedAt' => now()->toIso8601String(),
        ]);
    }
}

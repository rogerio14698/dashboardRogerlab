<?php

namespace App\Http\Controllers;

use App\Domain\Backup\BackupService;
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
use Throwable;

class DashboardController extends Controller
{
    public function backup(BackupService $backupService): View
    {
        return view('dashboard.backup', [
            'serverIp' => config('monitoring.server_ip'),
            'updatedAt' => now()->toIso8601String(),
            'sources' => $backupService->sources(),
            'latestBackup' => $backupService->latest(),
            'driveReady' => filled(config('backup.google_drive.refresh_token')) && filled(config('backup.google_drive.folder_id')),
        ]);
    }

    public function createBackup(Request $request, BackupService $backupService): BinaryFileResponse|RedirectResponse
    {
        $validated = $request->validate([
            'sources' => ['required', 'array', 'min:1'],
            'sources.*' => ['string', 'distinct'],
        ]);

        try {
            $path = $backupService->create($validated['sources']);

            return response()->download($path, basename($path), [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'backup' => 'No se pudo generar el backup. Revisa las rutas disponibles y los permisos del servidor.',
            ]);
        }
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

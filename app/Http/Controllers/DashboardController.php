<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\DockerContainer;
use App\Models\N8nExecution;
use App\Models\SeoCheck;
use App\Models\SubdomainCheck;
use App\Models\SystemMetric;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $latestUptime = SubdomainCheck::query()
            ->with('subdomain:id,name,url')
            ->latest('checked_at')
            ->get()
            ->unique('subdomain_id')
            ->values();

        return Inertia::render('Metrics', [
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

<?php

namespace App\Http\Controllers;

use App\Models\DockerContainer;
use App\Models\N8nExecution;
use App\Models\SeoCheck;
use App\Models\SystemMetric;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringModuleController extends Controller
{
    public function __invoke(string $module): Response
    {
        abort_unless(in_array($module, ['system-metrics', 'docker', 'uptime', 'seo', 'n8n'], true), 404);

        $props = match ($module) {
            'system-metrics' => ['metric' => SystemMetric::query()->latest('captured_at')->first()],
            'docker' => ['containers' => DockerContainer::query()->latest('captured_at')->limit(100)->get()],
            'seo' => ['checks' => SeoCheck::query()->with('subdomain:id,name')->latest('checked_at')->limit(100)->get()],
            'n8n' => ['executions' => N8nExecution::query()->latest('started_at')->limit(50)->get()],
            default => [],
        };

        return Inertia::render('Modules/' . ucfirst($module), $props);
    }
}

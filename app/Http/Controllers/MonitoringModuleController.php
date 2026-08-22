<?php

namespace App\Http\Controllers;

use App\Models\N8nExecution;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringModuleController extends Controller
{
    public function __invoke(string $module): Response
    {
        abort_unless(in_array($module, ['docker', 'uptime', 'seo', 'n8n'], true), 404);

        $props = $module === 'n8n'
            ? ['executions' => N8nExecution::query()->latest('started_at')->limit(50)->get()]
            : [];

        return Inertia::render('Modules/' . ucfirst($module), $props);
    }
}

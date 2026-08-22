<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class MonitoringModuleController extends Controller
{
    public function __invoke(string $module): Response
    {
        abort_unless(in_array($module, ['docker', 'uptime', 'seo', 'n8n'], true), 404);
        return Inertia::render('Modules/' . ucfirst($module));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;


class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        return view('dashboard.home', [
            'title' => 'Inicio',
            'description' => 'Panel de inicio del dashboard',
            'serverIp' => config('monitoring.server_ip'),
            'updatedAt' => now()->toIso8601String(),
             
            // Datos de actividad reciente, como ultimos logins, acciones realizadas, etc.
            'recentActivities' => [

            ]
        ]);
    }
}

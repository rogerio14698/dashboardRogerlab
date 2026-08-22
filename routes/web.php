<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringModuleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
    ? app(DashboardController::class)()
        : Inertia::render('Auth/Login');
})->name('dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/system-metrics', MonitoringModuleController::class)->defaults('module', 'system-metrics')->name('monitoring.system-metrics');
    Route::get('/docker', MonitoringModuleController::class)->defaults('module', 'docker')->name('monitoring.docker');
    Route::get('/uptime', MonitoringModuleController::class)->defaults('module', 'uptime')->name('monitoring.uptime');
    Route::get('/seo', MonitoringModuleController::class)->defaults('module', 'seo')->name('monitoring.seo');
    Route::get('/n8n', MonitoringModuleController::class)->defaults('module', 'n8n')->name('monitoring.n8n');
    Route::get('/{module}', MonitoringModuleController::class)->name('monitoring.module');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

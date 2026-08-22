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
    Route::get('/{module}', MonitoringModuleController::class)->name('monitoring.module');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

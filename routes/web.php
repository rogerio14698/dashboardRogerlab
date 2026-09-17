<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\GestionContenidoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\MonitoringModuleController;
use Illuminate\Support\Facades\Route;

//Redireccion inteligente a la raiz /
Route::get('/', function () {
    return auth()->check() 
    ? redirect()->route('dashboard')
    : redirect()->route('login');
});

//Rutas para usuarios no autenticados. Solo Login;
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {

    /*Inicio del gestor de contenido */
    Route::get('/gestor-contenido', [GestionContenidoController::class, 'index'])->name('gestor-contenido');
    Route::post('/gestor-contenido', [GestionContenidoController::class, 'store'])->name('gestor-contenido.store');
    Route::post('/gestor-contenido/connect', [GestionContenidoController::class, 'connect'])->name('gestor-contenido.connect');
    Route::post('/gestor-contenido/save-row', [GestionContenidoController::class, 'saveTableRow'])->name('gestor-contenido.save-row');

    //Fin del gestor de contenido



    // Pagina de inicio: una vista sencilla que sirve como punto de entrada.
    Route::get('/dashboard', HomeController::class)->name('dashboard');

    //Pagina de los subdomnios y dominios del usuario de Claudflare mediante API.
    Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');

    // Pagina con el resumen completo de metricas del servidor.
    Route::get('/metrics', DashboardController::class)->name('metrics');

    //Pagina del backup completo del sistema.
    Route::get('/backup', [DashboardController::class, 'backup'])->name('backup');
    Route::post('/backup', [DashboardController::class, 'createBackup'])->name('backup.create');
    Route::post('/backup/upload', [DashboardController::class, 'uploadBackup'])->name('backup.upload');
    Route::get('/backup/download/{backup}', [DashboardController::class, 'downloadBackup'])->name('backup.download');

    // Explorador de MariaDB y operaciones CRUD sobre la base y tabla seleccionadas.
    Route::get('/data-base', [DatabaseController::class, 'index'])->name('database.index');
    Route::post('/data-base/row', [DatabaseController::class, 'store'])->name('database.store');
    Route::patch('/data-base/row', [DatabaseController::class, 'update'])->name('database.update');
    Route::delete('/data-base/row', [DatabaseController::class, 'destroy'])->name('database.destroy');

    //Rutas para los modulos de monitoreo del servidor, cada modulo tiene su propia ruta y controlador.
    Route::get('/system-metrics', MonitoringModuleController::class)->defaults('module', 'system-metrics')->name('monitoring.system-metrics');
    Route::get('/docker', MonitoringModuleController::class)->defaults('module', 'docker')->name('monitoring.docker');
    Route::get('/uptime', MonitoringModuleController::class)->defaults('module', 'uptime')->name('monitoring.uptime');
    Route::get('/seo', MonitoringModuleController::class)->defaults('module', 'seo')->name('monitoring.seo');
    Route::get('/n8n', MonitoringModuleController::class)->defaults('module', 'n8n')->name('monitoring.n8n');



    //Modulo generico siempre al final, para cualquier modulo que no tenga ruta especifica, se le pasa el nombre del modulo como parametro.
    Route::get('/{module}', MonitoringModuleController::class)->name('monitoring.module');

    //Ruta para cerrar sesion, solo disponible para usuarios autenticados.
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

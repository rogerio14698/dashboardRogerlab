<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ruta para el Webhook de Auto-Despliegue (CI/CD Automatizado)
|--------------------------------------------------------------------------
|
| Esta ruta recibe las notificaciones 'POST' enviadas por GitHub cada vez
| que haces un 'git push' a la rama principal de tu repositorio.
|
| URL para GitHub: https://dashboard.rogerlab.es/api/deploy
|
*/

Route::post('/deploy', function (Request $request) {
    // El secreto es obligatorio: sin él, el endpoint no puede ejecutar comandos remotos.
    $githubSecret = config('services.github.deploy_secret');
    $githubSignature = $request->header('X-Hub-Signature-256');

    if (! is_string($githubSecret) || $githubSecret === '') {
        Log::critical('Webhook de despliegue deshabilitado: falta GITHUB_DEPLOY_SECRET.');

        return response()->json(['status' => 'error', 'message' => 'Webhook no configurado.'], 503);
    }

    $hash = 'sha256=' . hash_hmac('sha256', $request->getContent(), $githubSecret);
    if (! hash_equals($hash, (string) $githubSignature)) {
        Log::warning('Webhook de GitHub: firma de seguridad no válida.');

        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    try {
        // Cada comando recibe argumentos separados y se ejecuta desde la raíz del proyecto, sin shell.
        $commands = [
            ['git', 'pull', 'origin', 'master'],
            ['php8.3', 'artisan', 'migrate', '--force'],
            ['php8.3', 'artisan', 'optimize:clear'],
            ['php8.3', 'artisan', 'config:cache'],
        ];

        foreach ($commands as $command) {
            $process = Process::path(base_path())->timeout(300)->run($command);

            if (! $process->successful()) {
                Log::error('Error en auto-despliegue.', [
                    'command' => $command,
                    'exit_code' => $process->exitCode(),
                    'error' => $process->errorOutput(),
                ]);

                return response()->json(['status' => 'error', 'message' => 'Fallo al ejecutar el despliegue.'], 500);
            }
        }
    } catch (\Throwable $exception) {
        Log::error('Excepción en auto-despliegue.', ['message' => $exception->getMessage()]);

        return response()->json(['status' => 'error', 'message' => 'Error interno durante el despliegue.'], 500);
    }

    Log::info('Auto-despliegue ejecutado correctamente.');

    return response()->json(['status' => 'success', 'message' => 'Despliegue completado con éxito.']);
});
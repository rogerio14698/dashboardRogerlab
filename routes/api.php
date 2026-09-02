<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;

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
    // 1. Opcional: Validar el Token de Seguridad (Secret)
    // Para evitar que personas ajenas ejecuten tu despliegue visitando la URL,
    // puedes configurar un "Secret" en el Webhook de GitHub y definirlo en tu .env como DEPLOY_SECRET.
    $githubSecret = config('services.github.deploy_secret');
    $githubSignature = $request->header('X-Hub-Signature-256');

    if ($githubSecret) {
        $hash = 'sha256=' . hash_hmac('sha256', $request->getContent(), $githubSecret);
        if (!hash_equals($hash, (string) $githubSignature)) {
            Log::warning('Webhook de GitHub: Firma de seguridad no válida.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    // 2. Definir la ruta raíz de tu proyecto Laravel en el servidor
    $basePath = base_path();

    // 3. Cadena de comandos a ejecutar en el servidor
    // Se ejecutan en secuencia utilizando '&&' (si uno falla, la cadena se detiene)
    $command = implode(' && ', [
        "cd {$basePath}",
        'git pull origin master',       // Trae los últimos cambios de GitHub
        'php artisan migrate',          // Ejecuta migraciones 
        'php artisan config:cache',     // Guarda en caché la configuración para mayor velocidad
        'php artisan optimize:clear',   // Limpia las cachés viejas (vistas, rutas, eventos)
    ]);

    // 4. Ejecutar la orden en la terminal de Linux mediante el proceso de Symfony
    $process = Process::fromShellCommandline($command);
    $process->setTimeout(300); // Límite de tiempo de 5 minutos para completar el proceso
    $process->run();

    // 5. Verificar si el despliegue fue exitoso o devolvió algún error
    if (!$process->isSuccessful()) {
        Log::error('Error en Auto-Despliegue:', [
            'output' => $process->getErrorOutput(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Fallo al ejecutar el despliegue.',
            'error' => $process->getErrorOutput(),
        ], 500);
    }

    // Registrar en los logs de Laravel que el despliegue fue exitoso
    Log::info('Auto-despliegue ejecutado correctamente:', [
        'output' => $process->getOutput(),
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Despliegue completado con éxito.',
        'output' => trim($process->getOutput()),
    ]);
});
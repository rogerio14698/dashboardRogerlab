<?php

namespace App\Http\Controllers;

use App\Models\GestionContenido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GestionContenidoController extends Controller
{
    public function index(): View
    {
        $databases = $this->availableDatabases();

        return view('dashboard.gestorContenidoWebs.gestorContenido', [
            'webs' => GestionContenido::query()->where('is_active', true)->orderBy('name')->get(),
            'databases' => $databases,
            'selectedWeb' => null,
            'selectedDatabase' => null,
            'tables' => [],
            'selectedTable' => null,
            'tableColumns' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'database_name' => ['required', 'string', 'max:255'],
            'upload_path' => ['nullable', 'string', 'max:500'],
        ]);

        $databaseExists = in_array($validated['database_name'], $this->availableDatabases(), true);

        abort_unless($databaseExists, 422, 'La base de datos seleccionada no existe en el servidor.');

        GestionContenido::create([
            'name' => $validated['name'],
            'database_name' => $validated['database_name'],
            'upload_path' => $validated['upload_path'] ?? env('GESTOR_UPLOAD_BASE_PATH', '/var/www'),
            'driver' => env('GESTOR_DB_DRIVER', 'mysql'),
            'host' => env('GESTOR_DB_HOST', '127.0.0.1'),
            'port' => env('GESTOR_DB_PORT', 3306),
            'username' => env('GESTOR_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('GESTOR_DB_PASSWORD', env('DB_PASSWORD', '')),
            'is_active' => true,
        ]);

        return redirect()->route('gestor-contenido')->with('success', 'Web registrada correctamente.');
    }

    public function connect(Request $request): View
    {
        $web = GestionContenido::query()->findOrFail($request->input('web_id'));
        $selectedTable = $request->input('table');

        config(['database.connections.gestor_web' => $this->connectionConfig($web)]);
        DB::purge('gestor_web');

        $tables = collect(DB::connection('gestor_web')->select('SHOW TABLES'))
            ->map(fn ($row) => (array) $row)
            ->flatten()
            ->values()
            ->all();

        $tableColumns = [];
        if ($selectedTable) {
            $tableColumns = $this->tableColumnsFor('gestor_web', $selectedTable);
        }

        return view('dashboard.gestorContenidoWebs.gestorContenido', [
            'webs' => GestionContenido::query()->where('is_active', true)->orderBy('name')->get(),
            'databases' => $this->availableDatabases(),
            'selectedWeb' => $web,
            'selectedDatabase' => $web->database_name,
            'tables' => $tables,
            'selectedTable' => $selectedTable,
            'tableColumns' => $tableColumns,
        ]);
    }

    public function saveTableRow(Request $request)
    {
        $webId = $request->input('web_id');
        $table = $request->input('table');
        $values = $request->input('values', []);

        abort_unless($webId && $table, 422, 'Falta la web o la tabla a guardar.');

        $web = GestionContenido::query()->findOrFail($webId);
        config(['database.connections.gestor_web' => $this->connectionConfig($web)]);
        DB::purge('gestor_web');

        $columns = $this->tableColumnsFor('gestor_web', $table);

        $payload = [];
        foreach ($columns as $column) {
            $field = $column['name'];

            if ($request->hasFile($field)) {
                $payload[$field] = $this->storeUploadedFile($request->file($field), $web);
                continue;
            }

            if (! array_key_exists($field, $values)) {
                continue;
            }

            $payload[$field] = $values[$field];
        }

        abort_if($payload === [], 422, 'No hay datos para guardar en la tabla.');

        DB::connection('gestor_web')->table($table)->insert($payload);

        return redirect()->route('gestor-contenido.connect', ['web_id' => $webId, 'table' => $table])
            ->with('success', 'Registro añadido correctamente.');
    }

    protected function tableColumnsFor(string $connectionName, string $table): array
    {
        return collect(DB::connection($connectionName)->select('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`'))
            ->map(fn ($column) => [
                'name' => $column->Field,
                'type' => $column->Type,
                'null' => $column->Null,
                'default' => $column->Default,
                'extra' => $column->Extra,
            ])
            ->reject(fn (array $column) => in_array($column['name'], ['created_at', 'updated_at', 'deleted_at'], true))
            ->reject(fn (array $column) => str_contains(strtolower((string) $column['extra']), 'auto_increment'))
            ->values()
            ->all();
    }

    protected function storeUploadedFile($file, GestionContenido $web): string
    {
        if (! $file || ! $file->isValid()) {
            return '';
        }

        $folder = $web->upload_path ?: env('GESTOR_UPLOAD_BASE_PATH', '/var/www');
        $folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'blog';

        if (! is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $filename = time() . '-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->move($folder, $filename);

        $fullPath = $folder . DIRECTORY_SEPARATOR . $filename;
        $publicMarker = DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
        $publicIndex = stripos($fullPath, $publicMarker);

        if ($publicIndex !== false) {
            return trim(substr($fullPath, $publicIndex + strlen($publicMarker)), DIRECTORY_SEPARATOR);
        }

        $relative = str_replace($web->upload_path ?: env('GESTOR_UPLOAD_BASE_PATH', '/var/www'), '', $fullPath);

        return trim(str_replace(['\\', '//'], '/', $relative), '/');
    }

    protected function availableDatabases(): array
    {
        return collect(DB::select('SHOW DATABASES'))
            ->pluck('Database')
            ->filter(fn ($database) => ! in_array(strtolower($database), ['information_schema', 'performance_schema', 'mysql', 'sys'], true))
            ->values()
            ->all();
    }

    protected function connectionConfig(GestionContenido $web): array
    {
        if (strtolower($web->driver) === 'sqlite') {
            return [
                'driver' => 'sqlite',
                'database' => database_path($web->database_name . '.sqlite'),
                'prefix' => '',
            ];
        }

        return [
            'driver' => $web->driver ?? 'mysql',
            'host' => $web->host ?? '127.0.0.1',
            'port' => $web->port ?? 3306,
            'database' => $web->database_name,
            'username' => $web->username ?? env('DB_USERNAME', 'root'),
            'password' => $web->password ?? env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];
    }
}

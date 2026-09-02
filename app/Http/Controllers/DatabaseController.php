<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DatabaseController extends Controller
{
    /** Esquemas internos que no deben aparecer ni poder modificarse desde el panel. */
    private const SYSTEM_DATABASES = ['information_schema', 'performance_schema', 'mysql', 'sys'];

    /**
     * Muestra el explorador. La base, tabla, página y búsqueda llegan como query parameters.
     * Esto permite compartir una URL concreta como /data-base?database=app&table=users.
     */
    public function index(Request $request): Response
    {
        $databases = $this->databases();
        $database = $request->string('database')->toString();
        $table = $request->string('table')->toString();
        $tables = [];
        $columns = [];
        $rows = [];
        $pagination = null;

        if ($database !== '') {
            $this->ensureDatabaseExists($database, $databases);
            $tables = $this->tables($database);
        }

        if ($table !== '') {
            $this->ensureTableExists($table, $tables);
            $columns = $this->columns($database, $table);
            [$rows, $pagination] = $this->rows($database, $table, $columns, $request);
        }

        return Inertia::render('Database', compact(
            'databases', 'database', 'tables', 'table', 'columns', 'rows', 'pagination'
        ));
    }

    /** Inserta una fila usando únicamente las columnas reales y editables de la tabla. */
    public function store(Request $request): RedirectResponse
    {
        [$database, $table, $columns] = $this->selectedTable($request);
        $values = $this->writableValues($request, $columns, false);

        abort_if($values === [], 422, 'No hay valores para insertar.');

        DB::insert(
            sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->identifier($database), implode(', ', array_map($this->identifier(...), array_keys($values))), implode(', ', array_fill(0, count($values), '?'))),
            array_values($values),
        );

        return $this->backToTable($database, $table)->with('success', 'Registro creado correctamente.');
    }

    /** Actualiza una fila. La fila se identifica con la clave primaria descubierta mediante DESCRIBE. */
    public function update(Request $request): RedirectResponse
    {
        [$database, $table, $columns] = $this->selectedTable($request);
        $primaryKey = $this->primaryKey($columns);
        abort_if($primaryKey === null, 422, 'La tabla no tiene una clave primaria que permita editar filas.');

        $primaryValue = $request->input('primary_value');
        abort_if($primaryValue === null || $primaryValue === '', 422, 'Falta el valor de la clave primaria.');
        $values = $this->writableValues($request, $columns, true);
        abort_if($values === [], 422, 'No hay cambios para guardar.');

        $assignments = implode(', ', array_map(fn ($column) => $this->identifier($column) . ' = ?', array_keys($values)));
        DB::update(
            sprintf('UPDATE %s SET %s WHERE %s = ?', $this->qualifiedTable($database, $table), $assignments, $this->identifier($primaryKey)),
            [...array_values($values), $primaryValue],
        );

        return $this->backToTable($database, $table)->with('success', 'Registro actualizado correctamente.');
    }

    /** Elimina una fila identificada por su clave primaria. */
    public function destroy(Request $request): RedirectResponse
    {
        [$database, $table, $columns] = $this->selectedTable($request);
        $primaryKey = $this->primaryKey($columns);
        abort_if($primaryKey === null, 422, 'La tabla no tiene una clave primaria que permita eliminar filas.');

        $primaryValue = $request->input('primary_value');
        abort_if($primaryValue === null || $primaryValue === '', 422, 'Falta el valor de la clave primaria.');

        DB::delete(
            sprintf('DELETE FROM %s WHERE %s = ?', $this->qualifiedTable($database, $table), $this->identifier($primaryKey)),
            [$primaryValue],
        );

        return $this->backToTable($database, $table)->with('success', 'Registro eliminado correctamente.');
    }

    /** Obtiene todas las bases visibles para el usuario de MariaDB, excepto sus esquemas internos. */
    private function databases(): array
    {
        return collect(DB::select('SHOW DATABASES'))
            ->map(fn (object $row) => $row->{'Database'})
            ->reject(fn (string $name) => in_array(strtolower($name), self::SYSTEM_DATABASES, true))
            ->values()
            ->all();
    }

    /** Devuelve los nombres de tabla de una base cuya existencia ya se ha validado. */
    private function tables(string $database): array
    {
        return collect(DB::select('SHOW TABLES FROM ' . $this->identifier($database)))
            ->map(fn (object $row) => (string) array_values((array) $row)[0])
            ->values()
            ->all();
    }

    /** DESCRIBE aporta los tipos, nulabilidad, valores por defecto y clave primaria para la UI y el CRUD. */
    private function columns(string $database, string $table): array
    {
        return collect(DB::select('DESCRIBE ' . $this->qualifiedTable($database, $table)))
            ->map(fn (object $column) => [
                'name' => $column->Field,
                'type' => $column->Type,
                'nullable' => $column->Null === 'YES',
                'key' => $column->Key,
                'default' => $column->Default,
                'extra' => $column->Extra,
            ])
            ->all();
    }

    /** Consulta una página de filas y busca texto en todas las columnas, con valores siempre parametrizados. */
    private function rows(string $database, string $table, array $columns, Request $request): array
    {
        $perPage = 25;
        $page = max(1, $request->integer('page', 1));
        $search = trim($request->string('search')->toString());
        $where = '';
        $bindings = [];

        if ($search !== '' && $columns !== []) {
            $conditions = array_map(function (array $column) use (&$bindings, $search): string {
                $bindings[] = "%{$search}%";
                return 'CAST(' . $this->identifier($column['name']) . ' AS CHAR) LIKE ?';
            }, $columns);
            $where = ' WHERE ' . implode(' OR ', $conditions);
        }

        $qualifiedTable = $this->qualifiedTable($database, $table);
        $total = (int) DB::selectOne('SELECT COUNT(*) AS aggregate FROM ' . $qualifiedTable . $where, $bindings)->aggregate;
        $offset = ($page - 1) * $perPage;
        $rows = DB::select('SELECT * FROM ' . $qualifiedTable . $where . ' LIMIT ? OFFSET ?', [...$bindings, $perPage, $offset]);

        return [(array) $rows, [
            'currentPage' => $page,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
            'perPage' => $perPage,
            'total' => $total,
        ]];
    }

    /** Verifica base, tabla y columnas antes de crear SQL con identificadores dinámicos. */
    private function selectedTable(Request $request): array
    {
        $databases = $this->databases();
        $database = $request->string('database')->toString();
        $this->ensureDatabaseExists($database, $databases);
        $tables = $this->tables($database);
        $table = $request->string('table')->toString();
        $this->ensureTableExists($table, $tables);

        return [$database, $table, $this->columns($database, $table)];
    }

    /** Extrae solamente columnas existentes, excluyendo campos auto-incrementales y la clave primaria al editar. */
    private function writableValues(Request $request, array $columns, bool $updating): array
    {
        $input = $request->input('values', []);
        abort_unless(is_array($input), 422, 'Los valores deben enviarse como un objeto.');

        return collect($columns)
            ->reject(fn (array $column) => str_contains(strtolower($column['extra']), 'auto_increment'))
            ->reject(fn (array $column) => $updating && $column['key'] === 'PRI')
            ->filter(fn (array $column) => array_key_exists($column['name'], $input))
            ->mapWithKeys(function (array $column) use ($input): array {
                $value = $input[$column['name']];
                return [$column['name'] => $value === '' && $column['nullable'] ? null : $value];
            })
            ->all();
    }

    /** MariaDB permite usar acentos graves en identificadores; además se escapan antes de interpolarlos. */
    private function identifier(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    private function qualifiedTable(string $database, string $table): string
    {
        return $this->identifier($database) . '.' . $this->identifier($table);
    }

    private function ensureDatabaseExists(string $database, array $databases): void
    {
        abort_unless(in_array($database, $databases, true), 404, 'Base de datos no encontrada.');
    }

    private function ensureTableExists(string $table, array $tables): void
    {
        abort_unless(in_array($table, $tables, true), 404, 'Tabla no encontrada.');
    }

    private function primaryKey(array $columns): ?string
    {
        return collect($columns)->firstWhere('key', 'PRI')['name'] ?? null;
    }

    private function backToTable(string $database, string $table): RedirectResponse
    {
        return redirect()->route('database.index', compact('database', 'table'));
    }
}
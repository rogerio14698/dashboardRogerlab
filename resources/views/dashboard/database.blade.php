@extends('layouts.app')

@section('content')
<div class="page">
    @include('partials.header')
    <main class="page__content">
        <div class="database-layout">
            <aside class="panel database-sidebar">
                <form method="GET" action="{{ route('database.index') }}">
                    <label class="field" for="database">Base de datos
                        <select id="database" name="database" onchange="this.form.submit()">
                            <option value="">Selecciona una base</option>
                            @foreach ($databases as $name)
                                <option value="{{ $name }}" @selected($database === $name)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>
                <h2 class="eyebrow" style="margin-top: 1.5rem">Tablas</h2>
                <div class="table-list">
                    @foreach ($tables as $name)
                        <a class="{{ $table === $name ? 'is-active' : '' }}" href="{{ route('database.index', ['database' => $database, 'table' => $name]) }}">{{ $name }}</a>
                    @endforeach
                    @if ($database && count($tables) === 0)<p class="muted">No hay tablas.</p>@endif
                </div>
            </aside>

            <section>
                <div class="database-toolbar">
                    <div><p class="eyebrow">Explorador DB</p><h2>{{ $table ?: 'Selecciona una tabla' }}</h2></div>
                    @if ($table)<a class="button button--primary" href="#new-record">Nuevo registro</a>@endif
                </div>

                @if ($table)
                    <form method="GET" action="{{ route('database.index') }}" class="database-toolbar" style="margin-top: 1.5rem">
                        <input type="hidden" name="database" value="{{ $database }}"><input type="hidden" name="table" value="{{ $table }}">
                        <label class="field" style="flex: 1">Buscar en todas las columnas<input name="search" value="{{ request('search') }}" placeholder="Buscar..."></label>
                        <button class="button" type="submit">Buscar</button>
                    </form>
                    <div class="table-wrap" style="margin-top: 1rem">
                        <table class="database-table">
                            <thead><tr>@foreach ($columns as $column)<th>{{ $column['name'] }} <small>{{ $column['type'] }}</small></th>@endforeach<th>Acciones</th></tr></thead>
                            <tbody>
                            @forelse ($rows as $row)
                                @php $row = (array) $row; @endphp
                                <tr>
                                    @foreach ($columns as $column)
                                        @php $value = $row[$column['name']] ?? null; @endphp
                                        <td title="{{ is_object($value) || is_array($value) ? json_encode($value) : $value }}">{{ is_null($value) ? 'NULL' : (is_object($value) || is_array($value) ? json_encode($value) : $value) }}</td>
                                    @endforeach
                                    <td>
                                        @if ($primaryKey = collect($columns)->firstWhere('key', 'PRI')['name'] ?? null)
                                            <details><summary class="button">Editar</summary>
                                                @include('dashboard.partials.record-form', ['editing' => true, 'row' => $row, 'formId' => 'edit-' . md5((string) $row[$primaryKey]), 'primaryKey' => $primaryKey])
                                            </details>
                                            <form method="POST" action="{{ route('database.destroy') }}" style="margin-top: .5rem" onsubmit="return confirm('Esta accion eliminara el registro de forma permanente.')">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="database" value="{{ $database }}"><input type="hidden" name="table" value="{{ $table }}"><input type="hidden" name="primary_value" value="{{ $row[$primaryKey] }}">
                                                <button class="button" type="submit">Eliminar</button>
                                            </form>
                                        @else <span class="muted">Sin clave primaria</span> @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($columns) + 1 }}" class="muted">No se encontraron registros.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($pagination)
                        <div class="database-toolbar" style="margin-top: 1rem"><span class="muted">{{ $pagination['total'] }} registros</span><span><a class="button" href="{{ route('database.index', ['database' => $database, 'table' => $table, 'search' => request('search'), 'page' => max(1, $pagination['currentPage'] - 1)]) }}">Anterior</a> {{ $pagination['currentPage'] }} / {{ $pagination['lastPage'] }} <a class="button" href="{{ route('database.index', ['database' => $database, 'table' => $table, 'search' => request('search'), 'page' => min($pagination['lastPage'], $pagination['currentPage'] + 1)]) }}">Siguiente</a></span></div>
                    @endif
                    @include('dashboard.partials.record-form', ['editing' => false, 'row' => [], 'formId' => 'new-record', 'primaryKey' => null])
                @else
                    <div class="panel" style="margin-top: 1.5rem"><p class="muted">Selecciona una base de datos y después una tabla.</p></div>
                @endif
            </section>
        </div>
    </main>
</div>
@endsection

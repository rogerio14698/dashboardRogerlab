@extends('layouts.app')

@section('content')
    @include('partials.header')

    <section class="page__content">
        <div class="seccionarWeb">
            <h1>Gestor de Contenido Web</h1>
            <p>Selecciona la web que quieres gestionar. Cada web apunta a una base de datos distinta.</p>
        </div>

        <details class="panel" style="margin-bottom: 2rem;">
            <summary style="cursor: pointer; font-weight: 600;">Registrar nueva web</summary>
            <div style="margin-top: 1rem;">
                <form method="POST" action="{{ route('gestor-contenido.store') }}">
                    @csrf

                    <div style="display: grid; gap: 1rem; max-width: 600px;">
                        <label class="field">
                            Nombre de la web
                            <input type="text" name="name" placeholder="Portfolio, Web 2, etc." required>
                        </label>

                        <label class="field">
                            Base de datos
                            <select name="database_name" required>
                                <option value="">Selecciona una base de datos</option>
                                @foreach ($databases as $database)
                                    <option value="{{ $database }}">{{ $database }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="field">
                            Ruta de imágenes del proyecto
                            <input type="text" name="upload_path" value="/var/www" placeholder="/var/www/portfolioRogerlab/public/images/blog">
                        </label>
                    </div>

                    <button class="button button--primary" type="submit" style="margin-top: 1rem;">Guardar web</button>
                </form>
            </div>
        </details>

        <form method="POST" action="{{ route('gestor-contenido.connect') }}">
            @csrf

            <label class="field" for="web_id">
                Selecciona la web
                <select name="web_id" id="web_id" required>
                    <option value="">Selecciona una web</option>
                    @foreach ($webs as $web)
                        <option value="{{ $web->id }}" @selected($selectedWeb && $selectedWeb->id === $web->id)>
                            {{ $web->name }} — {{ $web->database_name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="field" for="table">
                Tabla
                <select name="table" id="table">
                    <option value="">Selecciona una tabla</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table }}" @selected($selectedTable === $table)>{{ $table }}</option>
                    @endforeach
                </select>
            </label>

            <button class="button button--primary" type="submit">Conectar</button>
        </form>

        @if ($selectedDatabase)
            <div class="panel" style="margin-top: 1.5rem;">
                <p class="muted">Conectado a la base de datos:</p>
                <h3>{{ $selectedDatabase }}</h3>
            </div>
        @endif

        @if ($selectedTable)
            <div class="panel" style="margin-top: 1.5rem;">
                <h3>Insertar en {{ $selectedTable }}</h3>
                <form method="POST" action="{{ route('gestor-contenido.save-row') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="web_id" value="{{ $selectedWeb->id }}">
                    <input type="hidden" name="table" value="{{ $selectedTable }}">

                    <div style="display: grid; gap: 1rem; max-width: 700px;">
                        @foreach ($tableColumns as $column)
                            @php
                                $fieldName = $column['name'];
                                $type = strtolower($column['type']);
                                $isBoolean = str_contains($type, 'tinyint(1)') || str_contains($type, 'boolean');
                                $isText = str_contains($type, 'text') || str_contains($type, 'json') || str_contains($type, 'varchar') || str_contains($type, 'char');
                                $isNumber = preg_match('/int|decimal|float|double|numeric|bigint/', $type);
                            @endphp

                            <label class="field">
                                {{ $fieldName }}
                                @if ($isBoolean)
                                    <select name="values[{{ $fieldName }}]">
                                        <option value="1">1</option>
                                        <option value="0">0</option>
                                    </select>
                                @elseif (strtolower($fieldName) === 'content' || str_contains(strtolower($fieldName), 'description') || str_contains(strtolower($fieldName), 'body'))
                                    <textarea class="tinymce-editor" name="values[{{ $fieldName }}]" rows="8"></textarea>
                                @elseif (strtolower($fieldName) === 'image' || str_contains(strtolower($fieldName), 'img') || str_contains(strtolower($fieldName), 'photo'))
                                    <input type="file" name="{{ $fieldName }}" accept="image/*">
                                @elseif ($isText)
                                    <textarea name="values[{{ $fieldName }}]" rows="4"></textarea>
                                @else
                                    <input type="{{ $isNumber ? 'number' : 'text' }}" name="values[{{ $fieldName }}]">
                                @endif
                            </label>
                        @endforeach
                    </div>

                    <button class="button button--primary" type="submit" style="margin-top: 1rem;">Guardar registro</button>
                </form>
            </div>
        @endif
    </section>

    @include('partials.footer')
@endsection

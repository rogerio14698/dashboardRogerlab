@extends('layouts.app')

@section('content')
    <div class="page">
        @include('partials.header')

        <main class="page__content">
            <div class="rejillaBaseDatos rejillaGestor">
                <aside class="panel barraLateralBaseDatos barraLateralGestor">
                    <div class="introduccionGestor">
                        <p class="etiquetaSuperior">Gestor Web</p>
                        <h2>Selecciona una web</h2>
                        <p class="textoSuave">Conecta una web registrada y después elige la tabla con la que quieres trabajar.
                        </p>
                    </div>

                    @include('partials.form-gestorContenido-SeleccionarWeb')

                    @if ($selectedDatabase)
                        <div class="estadoGestor panel">
                            <p class="textoSuave">Base conectada</p>
                            <h3>{{ $selectedDatabase }}</h3>
                        </div>
                    @endif

                    <h2 class="etiquetaSuperior">Tablas</h2>
                    <div class="listaTablas">
                        @forelse ($tables as $table)
                            <form method="POST" action="{{ route('gestor-contenido.connect') }}">
                                @csrf
                                <input type="hidden" name="web_id" value="{{ $selectedWeb->id ?? '' }}">
                                <input type="hidden" name="table" value="{{ $table }}">
                                <button class="botonTabla {{ $selectedTable === $table ? 'activo' : '' }}"
                                    type="submit">
                                    {{ $table }}
                                </button>
                            </form>
                        @empty
                            @if ($selectedWeb)
                                <p class="textoSuave">No hay tablas disponibles.</p>
                            @else
                                <p class="textoSuave">Conecta una web para ver sus tablas.</p>
                            @endif
                        @endforelse
                    </div>

                    <details class="panel registroGestor">
                        <summary style="cursor: pointer; font-weight: 600;">Registrar nueva web</summary>
                        <div style="margin-top: 1rem;">
                            @include('partials.form-gestorContenido-RegistrarWeb')
                        </div>
                    </details>

                    @if ($selectedWeb)
                        <form class="content-form__delete" action="{{ route('gestor-contenido.delete-web') }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="web_id" value="{{ $selectedWeb->id }}">
                            <button class="boton" type="submit">Borrar web</button>
                        </form>
                    @endif
                </aside>

                <section>
                    <div class="barraHerramientasBaseDatos">
                        <div>
                            <p class="etiquetaSuperior">Gestor de Contenido</p>
                            <h2>{{ $selectedTable ?: 'Selecciona una tabla' }}</h2>
                            @if ($selectedDatabase)
                                <p class="textoSuave">Web actual: {{ $selectedWeb->name }} | Base de datos:
                                    {{ $selectedDatabase }}</p>
                            @endif
                        </div>
                        @if ($selectedTable)
                            <a class="boton botonPrincipal" href="#new-record">Nuevo registro</a>
                        @endif
                    </div>

                    @if ($selectedTable)
                        <div class="panel" style="margin-top: 1.5rem;">
                            <div class="encabezadoFormulario">
                                <span class="subtituloFormulario">Nuevo registro</span>
                                <h2>Insertar en <strong>{{ $selectedTable }}</strong></h2>
                                <p>Completa los campos disponibles para añadir un registro a esta tabla.</p>
                            </div>
                            <form id="new-record" class="formularioContenido formularioDinamico" method="POST"
                                action="{{ route('gestor-contenido.save-row') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="web_id" value="{{ $selectedWeb->id }}">
                                <input type="hidden" name="selected_table" value="{{ $selectedTable }}">

                                <div class="formularioRegistro camposAutomaticos">
                                    <div class="camposRegistro camposFormulario">
                                        @foreach ($tableColumns as $column)
                                            @php
                                                $fieldName = $column['name'];
                                                $type = strtolower($column['type']);
                                                $rawValue = $column['default'] ?? '';
                                                $value =
                                                    is_object($rawValue) || is_array($rawValue)
                                                        ? json_encode($rawValue)
                                                        : $rawValue;
                                                $isBoolean =
                                                    str_contains($type, 'tinyint(1)') || str_contains($type, 'boolean');
                                                $isRichText =
                                                    strtolower($fieldName) === 'content' ||
                                                    str_contains(strtolower($fieldName), 'description') ||
                                                    str_contains(strtolower($fieldName), 'body');
                                                $isLongText = preg_match('/text|json/i', $type);
                                                $isFile =
                                                    strtolower($fieldName) === 'image' ||
                                                    str_contains(strtolower($fieldName), 'img') ||
                                                    str_contains(strtolower($fieldName), 'photo');
                                                $isNumeric = preg_match(
                                                    '/int|decimal|float|double|numeric|bigint/i',
                                                    $type,
                                                );
                                                $isDateTime = preg_match('/date|time|timestamp/i', $type);
                                                $isWide = $isRichText || $isLongText || $isFile;
                                                $inputType = $isDateTime
                                                    ? 'datetime-local'
                                                    : ($isNumeric
                                                        ? 'number'
                                                        : 'text');
                                            @endphp

                                            <label
                                                class="campo campoFormulario {{ $isWide ? 'campoFormularioAncho' : '' }}">
                                                <span class="etiquetaCampo">{{ $fieldName }}</span>
                                                <span
                                                    class="metadatoFormulario">{{ $column['type'] }}{{ $column['null'] === 'YES' ? ' · nullable' : '' }}</span>

                                                @if ($isBoolean)
                                                    <select name="values[{{ $fieldName }}]">
                                                        <option value="">Sin valor</option>
                                                        <option value="1">Si</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                @elseif ($isFile)
                                                    <span class="selectorArchivo">
                                                        <input type="file" name="{{ $fieldName }}" accept="image/*">
                                                        <span class="etiquetaArchivo">Seleccionar imagen</span>
                                                        <span class="ayudaArchivo">PNG, JPG o WEBP</span>
                                                    </span>
                                                @elseif ($isRichText)
                                                    <textarea class="tinymce-editor" name="values[{{ $fieldName }}]" rows="10">{{ old('values.' . $fieldName, $value) }}</textarea>
                                                @elseif ($isLongText)
                                                    <textarea name="values[{{ $fieldName }}]" rows="6">{{ old('values.' . $fieldName, $value) }}</textarea>
                                                @else
                                                    <input name="values[{{ $fieldName }}]" type="{{ $inputType }}"
                                                        value="{{ old('values.' . $fieldName, $value) }}">
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <button class="boton botonPrincipal" type="submit" style="margin-top: 1rem;">Guardar
                                    registro</button>
                            </form>
                        </div>
                    @else
                        <div class="panel" style="margin-top: 1.5rem">
                            <p class="textoSuave">Selecciona una web y después una tabla desde el panel lateral.</p>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection

<form class="selectorContenido" method="POST" action="{{ route('gestor-contenido.connect') }}">
    @csrf
    <label class="campo" for="web_id">
        Selecciona la web
        <select name="web_id" id="web_id" required>
            <option value="">Selecciona una web</option>
            @foreach ($webs as $web)
                <option value="{{ $web->id }}" @selected($selectedWeb && $selectedWeb->id === $web->id)>
                    {{ $web->name }} | {{ $web->database_name }}
                </option>
            @endforeach
        </select>
    </label>

    <button class="boton botonPrincipal" type="submit">Conectar</button>
</form>

@if ($webs->isNotEmpty())
    <div class="listaWebs">
        <p class="etiquetaSuperior">Webs registradas</p>
        @foreach ($webs as $web)
            <div class="elementoListaWeb {{ $selectedWeb && $selectedWeb->id === $web->id ? 'activo' : '' }}">
                <div>
                    <strong>{{ $web->name }}</strong>
                    <p class="textoSuave">{{ $web->database_name }}</p>
                </div>
                <form method="POST" action="{{ route('gestor-contenido.delete-web') }}" onsubmit="return confirm('Esta accion desactivara la web seleccionada.')">
                    @csrf
                    <input type="hidden" name="web_id" value="{{ $web->id }}">
                    <button class="boton" type="submit">Eliminar</button>
                </form>
            </div>
        @endforeach
    </div>
@endif

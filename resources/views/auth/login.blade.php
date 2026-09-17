@extends('layouts.app')

@section('content')
<main class="pagina paginaAutenticacion">
    <form method="POST" action="{{ route('login.store') }}" class="tarjetaFormulario formularioAutenticacion">
        @csrf
        <p class="etiquetaSuperior">Rogerlab</p>
        <h1>Acceso privado</h1>
        <p class="textoSuave">Solo el administrador puede entrar.</p>
        <label class="campo" for="email">Email
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        </label>
        @error('email') <p class="estadoPeligro">{{ $message }}</p> @enderror
        <label class="campo" for="password">Contrasena
            <input id="password" name="password" type="password" required>
        </label>
        @error('password') <p class="estadoPeligro">{{ $message }}</p> @enderror
        <label class="campo"><input name="remember" type="checkbox" value="1"> Recordarme</label>
        <button class="boton botonPrincipal" type="submit">Entrar</button>
    </form>
</main>
@endsection

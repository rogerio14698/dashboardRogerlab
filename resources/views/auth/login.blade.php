@extends('layouts.app')

@section('content')
<main class="page auth-page">
    <form method="POST" action="{{ route('login.store') }}" class="form-card auth-form">
        @csrf
        <p class="eyebrow">Rogerlab</p>
        <h1>Acceso privado</h1>
        <p class="muted">Solo el administrador puede entrar.</p>
        <label class="field" for="email">Email
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        </label>
        @error('email') <p class="status--danger">{{ $message }}</p> @enderror
        <label class="field" for="password">Contrasena
            <input id="password" name="password" type="password" required>
        </label>
        @error('password') <p class="status--danger">{{ $message }}</p> @enderror
        <label class="field"><input name="remember" type="checkbox" value="1"> Recordarme</label>
        <button class="button button--primary" type="submit">Entrar</button>
    </form>
</main>
@endsection

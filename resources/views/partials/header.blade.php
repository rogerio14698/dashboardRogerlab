<header class="encabezadoSitio">
    <div class="encabezadoSitioInterior">
        <div class="marca">
            <p class="marcaEtiqueta">Rogerlab |</p>
            <h1 class="marcaTitulo">Dashboard del servidor</h1>
        </div>
        <nav class="navegacionSitio" aria-label="Navegacion principal">
            <a class="enlaceNavegacion" href="{{ route('dashboard') }}">Inicio</a>
            <details>
                <summary>Servidores y Redes</summary>
                <div class="menuNavegacion">
                    <a href="{{ route('domains.index') }}">Domains</a>
                    <a href="{{ route('database.index') }}">Database</a>
                    <a href="{{ route('monitoring.docker') }}">Containers</a>
                    <a href="{{ route('monitoring.system-metrics') }}">System metrics</a>
                    <a href="{{ route('monitoring.uptime') }}">Uptime</a>
                    <a href="{{ route('metrics') }}">Metricas</a>
                    <a href="{{ route('backup') }}">Backup</a>
                </div>
            </details>
            <details>
                <summary>Gestor de contenido</summary>
                <div class="menuNavegacion">
                    {{-- PAra un futuro esto va a ser dinámico voya  generar tantos enlaces como webs que quiera gestionar su contenido. --}}
                    <a href="{{ route('gestor-contenido') }}">Gestor de contenido</a>

                    
                </div>
            </details>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="site-nav__link" type="submit">Cerrar sesion</button>
            </form>
        </nav>
    </div>
</header>

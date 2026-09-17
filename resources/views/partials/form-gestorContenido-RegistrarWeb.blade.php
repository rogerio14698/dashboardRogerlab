<form class="formularioContenido" method="POST" action="{{ route('gestor-contenido.store') }}">
                    @csrf

                    <div class="camposFormulario">
                        <label class="campo">
                            Nombre de la web
                            <input type="text" name="name" placeholder="Portfolio, Web 2, etc." required>
                        </label>

                        <label class="campo">
                            Base de datos
                            <select name="database_name" required>
                                <option value="">Selecciona una base de datos</option>
                                @foreach ($databases as $database)
                                    <option value="{{ $database }}">{{ $database }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="campo">
                            Ruta de imágenes del proyecto
                            <input type="text" name="upload_path" value="/var/www" placeholder="/var/www/portfolioRogerlab/public/images/blog">
                        </label>
                    </div>

                    <button class="boton botonPrincipal" type="submit" style="margin-top: 1rem;">Guardar web</button>
                </form>
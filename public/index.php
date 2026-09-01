<?php

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>whatIamListening</title>
  <link rel="stylesheet" href="./css/style.css">
</head>
<body>
  <main class="contenedor-principal">

    <!-- Tarjeta de Estado: Escuchando en tiempo real -->
    <article class="tarjeta-reproductor" id="spotify-card">
      <header class="tarjeta-encabezado">
        <h1 class="tarjeta-titulo">Escuchando ahora</h1>
      </header>

      <section class="reproductor-cuerpo">
        <figure class="portada-contenedor">
          <img id="track-cover" src="" alt="Portada del álbum" class="portada-imagen oculto">
        </figure>

        <section class="metadatos-pista">
          <p id="track-title" class="pista-titulo">Cargando estado...</p>
          <p id="track-artist" class="pista-artista"></p>
        </section>
      </section>
    </article>

    <!-- Módulo interactivo: Buscador y recomendación -->
    <section class="tarjeta-recomendacion">
      <header class="tarjeta-encabezado">
        <h2 class="tarjeta-subtitulo">Recomiéndame una canción</h2>
      </header>

      <form id="formulario-busqueda" class="formulario-buscador" onsubmit="event.preventDefault();">
        <fieldset class="grupo-campos">
          <legend class="sr-only">Buscador de pistas en Spotify</legend>
          <label for="input-busqueda" class="etiqueta-busqueda">Buscar en el catálogo:</label>
          <input 
            type="search" 
            id="input-busqueda" 
            class="campo-texto" 
            placeholder="Escribe el nombre de un tema o artista..." 
            autocomplete="off"
          >
          <ul id="lista-resultados" class="lista-desplegable" role="listbox"></ul>
        </fieldset>

        <output id="mensaje-estado" class="mensaje-retroalimentacion" for="input-busqueda"></output>
      </form>
    </section>

    <!-- Pie de página con enlace al repositorio plantilla -->
    <footer class="pie-pagina">
      <p>
        ¿Quieres este widget en tu web? 
        <a href="https://github.com/TU_USUARIO/TU_REPOSITORIO" target="_blank" rel="noopener noreferrer" class="enlace-repositorio">
          Clona la plantilla en GitHub
        </a>
      </p>
    </footer>

  </main>

  <script src="/public/js/app.js"></script>
</body>
</html>
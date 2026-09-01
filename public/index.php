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
<header class="cabecera-principal">
      <h1>whatIamListening</h1>
      <a href="about.html" class="enlace-acerca">Acerca de</a>
    </header>
    
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
        
        <!-- Botón de letras alineado a la derecha -->
        <button id="btn-letras" class="boton-letras oculto" aria-label="Mostrar letras" title="Ver letras">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
        </button>
      </section>
    </article>
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
        <a href="https://github.com/r3nz00/whatIamListening/" target="_blank" rel="noopener noreferrer" class="enlace-repositorio">
          Clona la plantilla en GitHub
        </a>
      </p>
    </footer>
  </main> <!-- Aquí termina tu contenedor principal -->

  <!-- Panel lateral para las letras -->
  <aside id="panel-letras" class="panel-lateral">
    <header class="tarjeta-encabezado">
      <h2 class="tarjeta-titulo">Letra</h2>
    </header>
    <article id="contenedor-letras" class="letras-scroll">
      <p id="texto-letras" class="mensaje-letras">Presiona el botón para buscar la letra...</p>
    </article>
  </aside>
  <script src="/public/js/app.js"></script>
</body>
</html>
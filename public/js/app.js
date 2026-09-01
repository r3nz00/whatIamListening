// Referencias a los elementos del HTML
const trackCover = document.getElementById("track-cover");
const trackTitle = document.getElementById("track-title");
const trackArtist = document.getElementById("track-artist");
const inputBusqueda = document.getElementById("input-busqueda");
const listaResultados = document.getElementById("lista-resultados");
const mensajeEstado = document.getElementById("mensaje-estado");

let temporizadorBusqueda = null;

// 1. Obtener estado de reproducción en tiempo real
async function actualizarEstadoEnVivo() {
  try {
    const respuesta = await fetch('../app/controlador/estado.php');
    const data = await respuesta.json();

    if (data.sonando) {
      trackCover.src = data.portada;
      trackCover.classList.remove("oculto");
      trackTitle.textContent = data.titulo;
      trackArtist.textContent = data.artista;
    } else {
      trackCover.classList.add("oculto");
      trackTitle.textContent = "Sin reproducción activa";
      trackArtist.textContent = "Spotify está en pausa";
    }
  } catch (error) {
    trackTitle.textContent = "Estado no disponible";
    trackArtist.textContent = "Verifica la consola de errores";
  }
}

// 2. Búsqueda conectada al controlador PHP
inputBusqueda.addEventListener("input", (evento) => {
  clearTimeout(temporizadorBusqueda);
  const consulta = evento.target.value.trim();

  if (consulta.length < 2) {
    listaResultados.innerHTML = "";
    return;
  }

  temporizadorBusqueda = setTimeout(async () => {
    try {
      const respuesta = await fetch(`../app/controlador/buscar.php?q=${encodeURIComponent(consulta)}`);
      const canciones = await respuesta.json();
      renderizarResultados(canciones);
    } catch (error) {
      mensajeEstado.textContent = "Error al buscar canciones.";
    }
  }, 300);
});

// Renderizar elementos de la lista desplegable
function renderizarResultados(canciones) {
  listaResultados.innerHTML = "";

  canciones.forEach((track) => {
    const item = document.createElement("li");
    item.className = "item-cancion";
    item.setAttribute("role", "option");

    const spanNombre = document.createElement("span");
    spanNombre.className = "item-nombre";
    spanNombre.textContent = track.name;

    const spanArtista = document.createElement("span");
    spanArtista.className = "item-artista";
    spanArtista.textContent = track.artists.map((a) => a.name).join(", ");

    item.appendChild(spanNombre);
    item.appendChild(spanArtista);

    item.addEventListener("click", () => enviarRecomendacion(track));
    listaResultados.appendChild(item);
  });
}

// 3. Enviar recomendación al controlador PHP
async function enviarRecomendacion(track) {
  listaResultados.innerHTML = "";
  inputBusqueda.value = "";
  mensajeEstado.textContent = "Enviando recomendación...";

  try {
    const respuesta = await fetch("../app/controlador/recomendar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        titulo: track.name,
        artista: track.artists.map((a) => a.name).join(", "),
        url: track.external_urls.spotify,
        portada: track.album.images[0]?.url || ""
      })
    });

    if (respuesta.ok) {
      mensajeEstado.textContent = `¡"${track.name}" recomendada con éxito!`;
    } else {
      mensajeEstado.textContent = "No se pudo procesar la recomendación.";
    }
  } catch (error) {
    mensajeEstado.textContent = "Error de conexión con el servidor.";
  }
}

// Iniciar la aplicación al cargar la página
actualizarEstadoEnVivo();
setInterval(actualizarEstadoEnVivo, 10000); // Actualiza cada 10 segundos
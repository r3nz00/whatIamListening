// Referencias HTML
const trackCover = document.getElementById("track-cover");
const trackTitle = document.getElementById("track-title");
const trackArtist = document.getElementById("track-artist");
const inputBusqueda = document.getElementById("input-busqueda");
const listaResultados = document.getElementById("lista-resultados");
const mensajeEstado = document.getElementById("mensaje-estado");
const btnLetras = document.getElementById("btn-letras");
const panelLetras = document.getElementById("panel-letras");
const contenedorLetras = document.getElementById("contenedor-letras");

let temporizadorBusqueda = null;

// Variables para el motor de letras
let letrasSincronizadas = [];
let progresoActualMs = 0;
let ultimaActualizacionTimestamp = 0;
let cancionSonando = false;
let animacionFrame = null;
let tituloAnterior = "";
let enCooldown = false;

// 1. Obtener estado en tiempo real
async function actualizarEstadoEnVivo() {
  try {
    const respuesta = await fetch('../app/controlador/estado.php');
    const data = await respuesta.json();
    
    cancionSonando = data.sonando;

    if (data.sonando) {
      trackCover.src = data.portada;
      trackCover.classList.remove("oculto");
      trackTitle.textContent = data.titulo;
      trackArtist.textContent = data.artista;
      btnLetras.classList.remove("oculto");
      
      // Guardamos el tiempo exacto para la sincronización
      progresoActualMs = data.progreso_ms;
      ultimaActualizacionTimestamp = Date.now();

      // Si cambió la canción, reseteamos las letras
      if (tituloAnterior !== data.titulo) {
        tituloAnterior = data.titulo;
        letrasSincronizadas = [];
        if (panelLetras.classList.contains("abierto")) {
          cargarLetras();
        }
      }
    } else {
      trackCover.classList.add("oculto");
      trackTitle.textContent = "Sin reproducción activa";
      trackArtist.textContent = "Spotify está en pausa";
      btnLetras.classList.add("oculto");
      panelLetras.classList.remove("abierto"); 
      btnLetras.classList.remove("activo");
    }
  } catch (error) {
    trackTitle.textContent = "Estado no disponible";
  }
}

// 2. Analizador de formato LRC (Extrae los tiempos - VERSIÓN MEJORADA)
function parsearLRC(lrcText) {
  const lineas = lrcText.split('\n');
  const resultado = [];
  // Regex más permisiva para evitar errores si la base de datos omite milisegundos
  const regex = /\[(\d+):(\d+(?:\.\d+)?)\](.*)/;

  lineas.forEach(linea => {
    const match = linea.match(regex);
    if (match) {
      const minutos = parseInt(match[1], 10);
      const segundos = parseFloat(match[2]);
      const texto = match[3].trim();
      if (texto) {
        resultado.push({
          tiempoSegundos: (minutos * 60) + segundos,
          texto: texto
        });
      }
    }
  });
  return resultado;
}

// 3. Motor de sincronización (60 frames por segundo)
function motorSincronizacion() {
  if (letrasSincronizadas.length > 0 && cancionSonando && panelLetras.classList.contains("abierto")) {
    const ahora = Date.now();
    // Calculamos dónde debería estar la canción basándonos en el último reporte del servidor
    const tiempoEstimadoSegundos = (progresoActualMs + (ahora - ultimaActualizacionTimestamp)) / 1000;

    let indiceActivo = -1;
    // Buscamos qué línea corresponde a este segundo
    for (let i = 0; i < letrasSincronizadas.length; i++) {
      if (tiempoEstimadoSegundos >= letrasSincronizadas[i].tiempoSegundos) {
        indiceActivo = i;
      } else {
        break; // Como están en orden cronológico, si nos pasamos, paramos.
      }
    }

    const lineasUI = contenedorLetras.querySelectorAll('.linea-letra');
    lineasUI.forEach((linea, index) => {
      if (index === indiceActivo) {
        if (!linea.classList.contains('activa')) {
          linea.classList.add('activa');
          // Desplazamiento suave para mantener la línea al centro
          linea.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      } else {
        linea.classList.remove('activa');
      }
    });
  }
  
  animacionFrame = requestAnimationFrame(motorSincronizacion);
}

// 4. Función para cargar letras desde el servidor
async function cargarLetras() {
  contenedorLetras.innerHTML = '<p class="linea-letra activa">Buscando letras...</p>';
  try {
    const artista = trackArtist.textContent;
    const titulo = trackTitle.textContent;
    
    const respuesta = await fetch(`../app/controlador/letras.php?artista=${encodeURIComponent(artista)}&titulo=${encodeURIComponent(titulo)}`);
    const data = await respuesta.json();

    contenedorLetras.innerHTML = ''; // Limpiamos contenedor

    if (data.syncedLyrics) {
      // PLAN A: Tiene letras sincronizadas
      letrasSincronizadas = parsearLRC(data.syncedLyrics);
      letrasSincronizadas.forEach(linea => {
        const p = document.createElement('p');
        p.className = 'linea-letra';
        p.textContent = linea.texto;
        contenedorLetras.appendChild(p);
      });
    } else if (data.plainLyrics) {
      // PLAN B: No hay sincronización, mostramos texto plano formateado
      const lineasPlanas = data.plainLyrics.split('\n');
      lineasPlanas.forEach(linea => {
        const p = document.createElement('p');
        p.className = 'linea-letra-plana'; // Usamos una clase nueva
        p.textContent = linea || '\u00A0'; // El código final respeta renglones vacíos
        contenedorLetras.appendChild(p);
      });
      letrasSincronizadas = [];
    } else {
      contenedorLetras.innerHTML = '<p class="linea-letra activa">No se encontraron letras.</p>';
    }
  } catch (error) {
    contenedorLetras.innerHTML = '<p class="linea-letra activa">Error de conexión.</p>';
  }
}

// Eventos de botones y buscadores
btnLetras.addEventListener("click", () => {
  panelLetras.classList.toggle("abierto");
  btnLetras.classList.toggle("activo");

  if (panelLetras.classList.contains("abierto") && letrasSincronizadas.length === 0) {
    cargarLetras();
  }
});

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

async function enviarRecomendacion(track) {
  // Verificamos si el usuario está bloqueado por el límite de tiempo
  if (enCooldown) {
    mensajeEstado.style.color = "red";
    mensajeEstado.textContent = "Espera 15 segundos antes de recomendar otra canción.";
    return;
  }

  listaResultados.innerHTML = "";
  inputBusqueda.value = "";
  mensajeEstado.style.color = "var(--color-spotify)";
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
      
      // Activamos el cooldown de 15 segundos
      enCooldown = true;
      inputBusqueda.disabled = true; // Bloqueamos la barra de búsqueda visualmente
      inputBusqueda.placeholder = "Espera 15 segundos...";
      
      setTimeout(() => {
        enCooldown = false;
        inputBusqueda.disabled = false;
        inputBusqueda.placeholder = "Escribe el nombre de un tema o artista...";
        mensajeEstado.textContent = ""; // Limpiamos el texto al terminar
      }, 15000); // 15000 milisegundos = 15 segundos

    } else {
      mensajeEstado.style.color = "red";
      mensajeEstado.textContent = "No se pudo procesar la recomendación.";
    }
  } catch (error) {
    mensajeEstado.style.color = "red";
    mensajeEstado.textContent = "Error de conexión con el servidor.";
  }
}

// Iniciar
actualizarEstadoEnVivo();
setInterval(actualizarEstadoEnVivo, 5000); // Bajamos a 5s para que los tiempos no se desincronicen
requestAnimationFrame(motorSincronizacion); // Arrancamos el loop del motor de letras
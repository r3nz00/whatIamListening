<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../modelo/SpotifyService.php';

$cuerpo = file_get_contents('php://input');
$datos = json_decode($cuerpo, true);

if (empty($datos['titulo']) || empty($datos['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$titulo = htmlspecialchars($datos['titulo']);
$artista = htmlspecialchars($datos['artista'] ?? 'Artista desconocido');
$enlace = filter_var($datos['url'], FILTER_SANITIZE_URL);
$portada = filter_var($datos['portada'] ?? '', FILTER_SANITIZE_URL);

// Resolvemos dinámicamente la clase para evitar errores de análisis cuando el servicio
// se carga con namespace o mediante autoload.
$spotifyServiceClass = class_exists('App\\SpotifyService')
    ? 'App\\SpotifyService'
    : (class_exists('SpotifyService') ? 'SpotifyService' : null);

if ($spotifyServiceClass === null || !is_callable([$spotifyServiceClass, 'guardarRecomendacion'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Servicio de recomendaciones no disponible']);
    exit;
}

$exito = call_user_func(
    [$spotifyServiceClass, 'guardarRecomendacion'],
    $titulo,
    $artista,
    $enlace,
    $portada
);

if ($exito) {
    echo json_encode(['mensaje' => 'Recomendación guardada con éxito']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la recomendación']);
}
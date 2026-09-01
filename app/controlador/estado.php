<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../modelo/SpotifyService.php';

$serviceClass = null;
if (class_exists('SpotifyService')) {
    $serviceClass = 'SpotifyService';
} elseif (class_exists('App\\Modelo\\SpotifyService')) {
    $serviceClass = 'App\\Modelo\\SpotifyService';
} elseif (class_exists('App\\SpotifyService')) {
    $serviceClass = 'App\\SpotifyService';
} elseif (class_exists('modelo\\SpotifyService')) {
    $serviceClass = 'modelo\\SpotifyService';
}

$estado = $serviceClass ? $serviceClass::obtenerReproduccionActual() : null;

if ($estado && isset($estado['item'])) {
    echo json_encode([
        'sonando' => $estado['is_playing'],
        'titulo' => $estado['item']['name'],
        'artista' => $estado['item']['artists'][0]['name'],
        'portada' => $estado['item']['album']['images'][0]['url'] ?? '',
        'url' => $estado['item']['external_urls']['spotify'] ?? ''
    ]);
} else {
    echo json_encode(['sonando' => false]);
}
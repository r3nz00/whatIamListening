<?php
header('Content-Type: application/json');

$artista = filter_input(INPUT_GET, 'artista', FILTER_SANITIZE_SPECIAL_CHARS);
$titulo = filter_input(INPUT_GET, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$artista || !$titulo) {
    echo json_encode(['error' => 'Faltan parámetros']);
    exit;
}

// Consultamos la API pública de Lrclib
$url = 'https://lrclib.net/api/get?' . http_build_query([
    'artist_name' => $artista,
    'track_name' => $titulo
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'whatIamListening-Widget/1.0');
$respuesta = curl_exec($ch);
curl_close($ch);

// Devolvemos la letra tal cual nos llega
echo $respuesta;
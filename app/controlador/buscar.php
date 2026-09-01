<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../modelo/SpotifyService.php';

// Capturamos lo que el usuario escribió en el buscador
$consulta = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS);

// Si la búsqueda está vacía o tiene menos de 2 letras, devolvemos una lista vacía
if (!$consulta || strlen(trim($consulta)) < 2) {
    echo json_encode([]);
    exit;
}

// Llamamos al modelo para buscar en la API de Spotify
$resultados = SpotifyService::buscarPistas($consulta);

// Devolvemos los resultados al frontend en formato JSON
echo json_encode($resultados);
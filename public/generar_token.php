<?php
// public/generar_token.php
require_once __DIR__ . '/../config/credenciales.php';

// Esta URL DEBE ser exactamente la misma que pusiste en el Dashboard de Spotify
$redirect_uri = 'http://127.0.0.1:3000/public/generar_token.php';;
$scope = 'user-read-currently-playing user-read-recently-played';

// Paso 1: Si no hay código en la URL, redirigimos a Spotify para iniciar sesión
if (!isset($_GET['code'])) {
    $url = 'https://accounts.spotify.com/authorize?' . http_build_query([
        'response_type' => 'code',
        'client_id' => SPOTIFY_CLIENT_ID,
        'scope' => $scope,
        'redirect_uri' => $redirect_uri
    ]);
    header('Location: ' . $url);
    exit;
}

// Paso 2: Si Spotify nos devuelve un código, lo cambiamos por los tokens
$code = $_GET['code'];

$ch = curl_init('https://accounts.spotify.com/api/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET),
    'Content-Type: application/x-www-form-urlencoded'
]);

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);

// Paso 3: Mostrar el Refresh Token en pantalla
if (isset($datos['refresh_token'])) {
    echo "<h1>¡Éxito!</h1>";
    echo "<p>Copia este Refresh Token y pégalo en tu archivo <strong>config/credenciales.php</strong>:</p>";
    echo "<textarea style='width: 100%; height: 100px; font-family: monospace;'>" . $datos['refresh_token'] . "</textarea>";
} else {
    echo "<h1>Error</h1>";
    echo "<pre>" . print_r($datos, true) . "</pre>";
}
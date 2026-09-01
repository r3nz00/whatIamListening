<?php
// Requerimos las credenciales privadas
require_once __DIR__ . '/../../config/credenciales.php';

class SpotifyService {
    
    // =========================================================
    // 1. MÉTODOS PARA EL BUSCADOR (Client Credentials)
    // =========================================================
    
    private static function obtenerToken() {
        $ch = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET)
        ]);

        $respuesta = curl_exec($ch);
        curl_close($ch);

        $datos = json_decode($respuesta, true);
        return $datos['access_token'] ?? null;
    }

    public static function buscarPistas($termino) {
        $token = self::obtenerToken();
        if (!$token) return [];

        $url = 'https://api.spotify.com/v1/search?q=' . urlencode($termino) . '&type=track&limit=5';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);

        $respuesta = curl_exec($ch);
        curl_close($ch);

        $datos = json_decode($respuesta, true);
        return $datos['tracks']['items'] ?? [];
    }

    // =========================================================
    // 2. MÉTODOS PARA ESTADO EN TIEMPO REAL (Refresh Token)
    // =========================================================

    private static function obtenerTokenUsuario() {
        $ch = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => SPOTIFY_REFRESH_TOKEN
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET),
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $respuesta = curl_exec($ch);
        curl_close($ch);
        
        $datos = json_decode($respuesta, true);
        return $datos['access_token'] ?? null;
    }

    public static function obtenerReproduccionActual() {
        $token = self::obtenerTokenUsuario();
        if (!$token) return null;

        $ch = curl_init('https://api.spotify.com/v1/me/player/currently-playing');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);

        $respuesta = curl_exec($ch);
        $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Spotify devuelve 204 cuando no hay música sonando
        if ($codigoHttp === 204 || empty($respuesta)) {
            return ['is_playing' => false];
        }

        return json_decode($respuesta, true);
    }

    // =========================================================
    // 3. MÉTODO PARA GUARDAR RECOMENDACIONES (JSON Local)
    // =========================================================

    public static function guardarRecomendacion($titulo, $artista, $enlace, $portada) {
        $rutaArchivo = __DIR__ . '/../../recomendaciones.json';
        
        $nuevaRecomendacion = [
            'fecha' => date('Y-m-d H:i:s'),
            'titulo' => $titulo,
            'artista' => $artista,
            'enlace' => $enlace,
            'portada' => $portada
        ];

        $datosActuales = [];
        
        if (file_exists($rutaArchivo)) {
            $jsonActual = file_get_contents($rutaArchivo);
            $datosActuales = json_decode($jsonActual, true) ?? [];
        }

        array_unshift($datosActuales, $nuevaRecomendacion);

        $exito = file_put_contents($rutaArchivo, json_encode($datosActuales, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        return $exito !== false;
    }
}
?>
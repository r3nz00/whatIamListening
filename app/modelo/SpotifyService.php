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

        // 1. Buscamos qué está sonando ahora en vivo
        $ch = curl_init('https://api.spotify.com/v1/me/player/currently-playing');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        $respuesta = curl_exec($ch);
        $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Si está sonando algo ahora mismo, lo devolvemos
        if ($codigoHttp !== 204 && !empty($respuesta)) {
            $datos = json_decode($respuesta, true);
            if (isset($datos['item'])) return $datos;
        }

        // 2. Si está en pausa, consultamos la última canción del historial
        $ch2 = curl_init('https://api.spotify.com/v1/me/player/recently-played?limit=1');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        $respuesta2 = curl_exec($ch2);
        curl_close($ch2);

        $datos2 = json_decode($respuesta2, true);
        
        // Si hay historial, engañamos al frontend mandándolo como si estuviera en pausa
        if (isset($datos2['items']) && count($datos2['items']) > 0) {
            $ultimoTrack = $datos2['items'][0]['track'];
            return [
                'is_playing' => false,
                'item' => $ultimoTrack
            ];
        }

        return ['is_playing' => false];
    }

 // =========================================================
    // 3. MÉTODO PARA GUARDAR RECOMENDACIONES Y NOTIFICAR
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
        
        // Si se guardó en el JSON con éxito, enviamos el mensaje
        if ($exito) {
            self::notificarTelegram($titulo, $artista, $enlace);
        }
        
        return $exito !== false;
    }

    // Nueva función que conecta con la API de Telegram
    private static function notificarTelegram($titulo, $artista, $enlace) {
        if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID')) return;

        $texto = "🎵 <b>¡Nueva recomendación musical!</b>\n\n";
        $texto .= "<b>Tema:</b> " . $titulo . "\n";
        $texto .= "<b>Artista:</b> " . $artista . "\n";
        $texto .= "<a href='" . $enlace . "'>Escuchar en Spotify</a>";

        $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $texto,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false // false = muestra la previsualización del enlace
        ]));
        curl_exec($ch);
        curl_close($ch);
    }
}
?>
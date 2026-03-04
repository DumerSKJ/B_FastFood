<?php

namespace App;

class DatabaseConfig
{
    public static function getSessionDuration(): int
    {
        return (int) $_ENV['SESSION_DURATION'];
    }

    public static function getAppUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Si .env tiene una URL definida, la usamos, pero aseguramos que el protocolo coincida con el actual
        // para evitar bloqueos mixtos de contenido si el usuario cambia manual entre http/https
        if (isset($_ENV['APP_URL'])) {
            $envUrl = rtrim($_ENV['APP_URL'], '/');
            // Reemplazamos el protocolo de la URL enviada por el real detectado
            return preg_replace('/^https?:\/\//', $protocol, $envUrl);
        }

        return $protocol . $host . '/base_test';
    }

    public static function getUpdateCheckClicks(): int
    {
        return (int) ($_ENV['UPDATE_CHECK_CLICKS'] ?? 10);
    }
}

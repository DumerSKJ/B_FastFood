<?php

namespace App\Helpers;

class Response
{
    /**
     * Envía una respuesta JSON y finaliza la ejecución
     * 
     * @param mixed $data
     * @param int $status
     * @return void
     */
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

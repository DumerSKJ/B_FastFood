<?php

namespace App\Helpers;

class Request
{
    /**
     * Obtiene los datos de entrada de la petición (POST JSON o GET)
     * 
     * @return array
     */
    public static function input(): array
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST') {
            $raw = file_get_contents("php://input");
            return json_decode($raw, true) ?? $_POST;
        }
        return $_GET;
    }
}

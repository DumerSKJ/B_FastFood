<?php
declare(strict_types=1);

namespace App;

use \PDO;
use \PDOException;

class DatabaseManager
{
    private $db1 = null;
    private $config;

    public function __construct()
    {
        // Guardamos la configuración pero NO conectamos todavía (Lazy Loading)
// Soporte dual para prefijos DB1_ (Legacy/Multi-DB) y DB_ (Estándar/.env.example)
        $this->config = [
            'host' => $_ENV['DB1_HOST'] ?? $_ENV['DB_HOST'] ?? 'localhost',
            'name' => $_ENV['DB1_NAME'] ?? $_ENV['DB_NAME'] ?? '',
            'user' => $_ENV['DB1_USER'] ?? $_ENV['DB_USER'] ?? 'root',
            'pass' => $_ENV['DB1_PASS'] ?? $_ENV['DB_PASS'] ?? '',
            'port' => $_ENV['DB1_PORT'] ?? $_ENV['DB_PORT'] ?? 3306,
        ];
    }

    public function getDB1()
    {
        if ($this->db1 === null) {
            $this->connect();
        }
        return $this->db1;
    }

    private function connect()
    {
        $host = $this->config['host'];
        $name = $this->config['name'];
        $user = $this->config['user'];
        $pass = $this->config['pass'];
        $port = $this->config['port'];

        try {
            $this->db1 = new PDO(
                "pgsql:host=$host;port=$port;dbname=$name",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_CASE => PDO::CASE_LOWER
                ]
            );
        } catch (PDOException $e) {
            // Lanzamos excepción en lugar de morir, para que el invocador pueda capturarla (o devolver JSON)
            throw new \Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}
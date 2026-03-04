<?php
declare(strict_types=1);

namespace App\Repositories\Auth;

use App\DatabaseManager;
use PDO;

class AuthRepository
{
    private PDO $db;

    public function __construct(private DatabaseManager $dbManager)
    {
        $this->db = $dbManager->getDB1();
    }

    public function getUserByUsername(string $username): ?array
    {
        $sql = "SELECT 
                    u.idusuario, 
                    u.idrolfk, 
                    u.usuario, 
                    u.clave, 
                    u.estado,
                    r.nombrerol as rol,
                    p.nombres,
                    p.apellidos
                FROM usuarios u
                JOIN roles r ON u.idrolfk = r.idrol
                JOIN personal p ON u.idpersonalfk = p.idpersonal
                WHERE u.usuario = :usuario";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}

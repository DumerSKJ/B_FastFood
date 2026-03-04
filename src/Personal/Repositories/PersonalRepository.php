<?php
declare(strict_types=1);

namespace App\Personal\Repositories;

use App\DatabaseManager;
use PDO;

class PersonalRepository
{
    private PDO $db;

    public function __construct(private DatabaseManager $dbManager)
    {
        $this->db = $dbManager->getDB1();
    }

    public function getPersonalList(): array
    {
        // Excluir personal con ID 1 (Desarrollador)
        $sql = "SELECT idPersonal AS \"idPersonal\", dni, nombres, apellidos, correo, telefono, fechaRegistro AS \"fechaRegistro\" 
                FROM personal 
                WHERE idPersonal != 1 
                ORDER BY idPersonal DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO personal (dni, nombres, apellidos, correo, telefono) VALUES (:dni, :nombres, :apellidos, :correo, :telefono)";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            'dni' => $data['dni'] ?? null,
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'correo' => $data['correo'] ?? null,
            'telefono' => $data['telefono'] ?? null
        ]);
        return $res ? (int) $this->db->lastInsertId() : 0;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE personal SET dni = :dni, nombres = :nombres, apellidos = :apellidos, correo = :correo, telefono = :telefono WHERE idPersonal = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'dni' => $data['dni'] ?? null,
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'correo' => $data['correo'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'id' => $id
        ]);
    }

    /* 
     * El borrado fÃ­sico estÃ¡ prohibido por regla de negocio.
     */
}


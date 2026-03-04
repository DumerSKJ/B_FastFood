<?php
declare(strict_types=1);

namespace App\Rol\Repositories;

use App\DatabaseManager;
use PDO;

class RolRepository
{
    private PDO $db;

    public function __construct(private DatabaseManager $dbManager)
    {
        $this->db = $dbManager->getDB1();
    }

    public function getRolesList(): array
    {
        // El Desarrollador (ID 1) no se muestra en la lista comÃºn
        $sql = "SELECT idrol, nombrerol, rolsubmodulo, fecharegistro FROM roles WHERE idrol != 1 ORDER BY idrol ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO roles (nombrerol, rolsubmodulo) VALUES (:nombreRol, :rolSubModulo)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nombreRol' => $data['nombreRol'],
            'rolSubModulo' => $data['rolSubModulo'] ?? null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if ($id === 1)
            return false; // No se permite editar al Desarrollador
        $sql = "UPDATE roles SET nombrerol = :nombreRol, rolsubmodulo = :rolSubModulo WHERE idrol = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nombreRol' => $data['nombreRol'],
            'rolSubModulo' => $data['rolSubModulo'] ?? null,
            'id' => $id
        ]);
    }

    /* 
     * El borrado fÃ­sico estÃ¡ prohibido por regla de negocio.
     * No se implementa DELETE sino inactivaciÃ³n si la tabla tuviera campo estado.
     */
}


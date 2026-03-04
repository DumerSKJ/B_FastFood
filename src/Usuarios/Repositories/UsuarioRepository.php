<?php
declare(strict_types=1);

namespace App\Usuarios\Repositories;

use App\DatabaseManager;
use PDO;

class UsuarioRepository
{
    private PDO $db;

    public function __construct(private DatabaseManager $dbManager)
    {
        $this->db = $dbManager->getDB1();
    }

    public function getListaCompleta(): array
    {
        $sql = "SELECT 
                    u.idusuario, 
                    u.usuario, 
                    u.idrolfk,
                    u.estado,
                    r.nombrerol as rol,
                    p.nombres,
                    p.apellidos,
                    p.correo,
                    u.fecharegistro
                FROM usuarios u
                JOIN roles r ON u.idrolfk = r.idrol
                JOIN personal p ON u.idpersonalfk = p.idpersonal
                WHERE u.idrolfk != 1
                ORDER BY u.idusuario DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO usuarios (idrolfk, idpersonalfk, usuario, clave, estado) 
                VALUES (:idrolfk, :idpersonalfk, :usuario, :clave, 'Activo')";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            'idrolfk' => $data['idRolFK'],
            'idpersonalfk' => $data['idPersonalFK'],
            'usuario' => $data['usuario'],
            'clave' => password_hash($data['clave'], PASSWORD_DEFAULT)
        ]);
        return $success ? (int) $this->db->lastInsertId() : 0;
    }

    public function update(int $id, array $data): bool
    {
        if ($id === 1)
            return false; // Dev no editable

        $fields = [];
        $params = ['id' => $id];

        if (isset($data['usuario'])) {
            $fields[] = "usuario = :usuario";
            $params['usuario'] = $data['usuario'];
        }
        if (isset($data['estado'])) {
            $fields[] = "estado = :estado";
            $params['estado'] = $data['estado'];
        }
        if (isset($data['idRolFK'])) {
            $fields[] = "idrolfk = :idrolfk";
            $params['idrolfk'] = $data['idRolFK'];
        }
        if (!empty($data['clave'])) {
            $fields[] = "clave = :clave";
            $params['clave'] = password_hash($data['clave'], PASSWORD_DEFAULT);
        }

        if (empty($fields))
            return true;

        $sql = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE idusuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function isUsernameAvailable(string $username, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE usuario = :username AND idusuario != :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username, 'id' => $excludeId]);
        return (int) $stmt->fetchColumn() === 0;
    }

    public function getPermisosUsuario(int $idUsuario): array
    {
        $sql = "SELECT idmodulosubmodulofk FROM usuario_permisos WHERE idusuariofk = :idUsuario AND acceso = true";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idUsuario' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function syncPermisos(int $idUsuario, array $permisosIds): bool
    {
        try {
            $this->db->beginTransaction();

            // En lugar de eliminar, desactivamos todos los accesos actuales para este usuario
            $stmt = $this->db->prepare("UPDATE usuario_permisos SET acceso = false WHERE idusuariofk = :id");
            $stmt->execute(['id' => $idUsuario]);

            // Insertar nuevos o actualizar existentes a acceso = 1
            if (!empty($permisosIds)) {
                $sql = "INSERT INTO usuario_permisos (idusuariofk, idmodulosubmodulofk, acceso) 
                        VALUES (:userId, :relId, true) 
                        ON CONFLICT (idusuariofk, idmodulosubmodulofk) DO UPDATE SET acceso = true";
                $stmt = $this->db->prepare($sql);
                foreach ($permisosIds as $relId) {
                    $stmt->execute(['userId' => $idUsuario, 'relId' => $relId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            // Log error for debugging if needed
            return false;
        }
    }

    /**
     * Aplica los permisos predeterminados de un rol a un usuario
     * Lee el campo rolSubModulo del rol y crea los registros en usuario_permisos
     */
    public function aplicarPermisosDeRol(int $idRol, int $idUsuario): bool
    {
        try {
            // 1. Obtener los permisos predeterminados del rol
            $sql = "SELECT rolsubmodulo FROM roles WHERE idrol = :idRol";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['idRol' => $idRol]);
            $rolData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$rolData || empty($rolData['rolsubmodulo'])) {
                return true; // No hay permisos predeterminados, no es error
            }

            // 2. Decodificar el JSON
            $permisosIds = json_decode($rolData['rolsubmodulo'], true);
            if (!is_array($permisosIds) || empty($permisosIds)) {
                return true;
            }

            // 3. Insertar los permisos
            $this->db->beginTransaction();
            $sql = "INSERT INTO usuario_permisos (idusuariofk, idmodulosubmodulofk, acceso) 
                    VALUES (:userId, :relId, true) 
                    ON CONFLICT (idusuariofk, idmodulosubmodulofk) DO UPDATE SET acceso = true";
            $stmt = $this->db->prepare($sql);

            foreach ($permisosIds as $relId) {
                $stmt->execute(['userId' => $idUsuario, 'relId' => (int) $relId]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}


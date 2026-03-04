<?php
namespace App\Menu\Repositories;

use App\DatabaseManager;
use PDO;

class MenuRepository
{
    private $db;

    public function __construct(DatabaseManager $dbManager)
    {
        $this->db = $dbManager->getDB1();
    }

    /**
     * Obtiene la lista base de relaciones MÃ³dulo-SubmÃ³dulo con metadata completa
     */
    public function getListaMenuRelaciones(): array
    {
        $sql = "SELECT 
                    msm.idmodulosubmodulo,
                    m.idmodulo,
                    m.nombremodulo,
                    m.folder_name as modfolder,
                    m.icono as modicono,
                    m.orden as modorden,
                    s.idsubmodulo,
                    s.nombresubmodulo,
                    s.folder_name as subfolder,
                    s.view_key,
                    s.icono as subicono,
                    msm.orden as relorden
                FROM modulos m
                LEFT JOIN modulo_sub_modulo msm ON m.idmodulo = msm.idmodulofk
                LEFT JOIN sub_modulos s ON msm.idsubmodulofk = s.idsubmodulo
                ORDER BY m.orden ASC, msm.orden ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getModulos(): array
    {
        $sql = "SELECT idmodulo, nombremodulo, folder_name, icono, orden FROM modulos ORDER BY orden ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateModulo(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        foreach ($data as $key => $value) {
            $column = strtolower($key);
            $fields[] = "$column = :$column";
            $params[$column] = $value;
        }
        $sql = "UPDATE modulos SET " . implode(', ', $fields) . " WHERE idmodulo = :id";
        return $this->db->prepare($sql)->execute($params);
    }

    public function updateSubModulo(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        foreach ($data as $key => $value) {
            $column = strtolower($key);
            $fields[] = "$column = :$column";
            $params[$column] = $value;
        }
        $sql = "UPDATE sub_modulos SET " . implode(', ', $fields) . " WHERE idsubmodulo = :id";
        return $this->db->prepare($sql)->execute($params);
    }

    public function updateRelacion(int $id, array $data): bool
    {
        $fields = ["orden = :orden"];
        $params = ['id' => $id, 'orden' => $data['orden']];

        if (isset($data['idModuloFK'])) {
            $fields[] = "idmodulofk = :modId";
            $params['modId'] = $data['idModuloFK'];
        }

        $sql = "UPDATE modulo_sub_modulo SET " . implode(", ", $fields) . " WHERE idmodulosubmodulo = :id";
        return $this->db->prepare($sql)->execute($params);
    }

    public function createModulo(array $data): int
    {
        $sql = "INSERT INTO modulos (nombremodulo, folder_name, icono, orden) VALUES (:nombre, :folder, :icono, :orden)";
        $this->db->prepare($sql)->execute([
            'nombre' => $data['nombreModulo'],
            'folder' => $data['folder_name'],
            'icono' => $data['icono'],
            'orden' => $data['orden']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function createSubModulo(array $data): int
    {
        $sql = "INSERT INTO sub_modulos (nombresubmodulo, folder_name, view_key, icono) VALUES (:nombre, :folder, :view, :icono)";
        $this->db->prepare($sql)->execute([
            'nombre' => $data['nombreSubModulo'],
            'folder' => $data['folder_name'],
            'view' => $data['view_key'],
            'icono' => $data['icono']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function linkModuloSubModulo(int $idModulo, int $idSubModulo, int $orden): bool
    {
        $sql = "INSERT INTO modulo_sub_modulo (idmodulofk, idsubmodulofk, orden) VALUES (:mod, :sub, :orden)";
        return $this->db->prepare($sql)->execute([
            'mod' => $idModulo,
            'sub' => $idSubModulo,
            'orden' => $orden
        ]);
    }
}


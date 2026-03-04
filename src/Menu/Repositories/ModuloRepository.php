<?php
namespace App\Menu\Repositories;

use App\DatabaseManager;
use PDO;

class ModuloRepository
{
    private $db;

    public function __construct(DatabaseManager $dbManager)
    {
        $this->db = $dbManager->getDB1();
    }

    /**
     * Obtiene el Ã¡rbol de mÃ³dulos y submÃ³dulos permitidos para un usuario
     */
    public function getMenuUsuario(int $idUsuario): array
    {
        // 0. Verificar si el usuario es Desarrollador (Rol 1) para darle acceso TOTAL
        $isDev = false;
        $checkSql = "SELECT idRolFK FROM usuarios WHERE idUsuario = :id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute(['id' => $idUsuario]);
        if ((int) $checkStmt->fetchColumn() === 1) {
            $isDev = true;
        }

        if ($isDev) {
            // Consulta para Desarrollador: Trae TODO lo que exista vinculado en modulo_sub_modulo
            $sql = "SELECT 
                        m.idmodulo, 
                        m.nombremodulo, 
                        m.icono as moduloicono, 
                        s.idsubmodulo, 
                        s.nombresubmodulo, 
                        s.view_key, 
                        s.icono as submoduloicono
                    FROM modulos m
                    JOIN modulo_sub_modulo msm ON m.idmodulo = msm.idmodulofk
                    JOIN sub_modulos s ON msm.idsubmodulofk = s.idsubmodulo
                    ORDER BY m.orden ASC, msm.orden ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            // Consulta Normal: Filtrado por tabla usuario_permisos
            $sql = "SELECT 
                        m.idmodulo, 
                        m.nombremodulo, 
                        m.icono as moduloicono, 
                        s.idsubmodulo, 
                        s.nombresubmodulo, 
                        s.view_key, 
                        s.icono as submoduloicono
                    FROM modulos m
                    JOIN modulo_sub_modulo msm ON m.idmodulo = msm.idmodulofk
                    JOIN sub_modulos s ON msm.idsubmodulofk = s.idsubmodulo
                    JOIN usuario_permisos p ON msm.idmodulosubmodulo = p.idmodulosubmodulofk
                    WHERE p.idusuariofk = :idUsuario 
                    AND p.acceso = true
                    ORDER BY m.orden ASC, msm.orden ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['idUsuario' => $idUsuario]);
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por mÃ³dulo para facilitar el renderizado
        $menu = [];
        foreach ($rows as $row) {
            $idM = $row['idmodulo'];
            if (!isset($menu[$idM])) {
                $menu[$idM] = [
                    'id' => $idM,
                    'nombre' => $row['nombremodulo'],
                    'icono' => $row['moduloicono'],
                    'submodulos' => []
                ];
            }
            $menu[$idM]['submodulos'][] = [
                'id' => $row['idsubmodulo'],
                'nombre' => $row['nombresubmodulo'],
                'view' => $row['view_key'],
                'icono' => $row['submoduloicono']
            ];
        }

        return array_values($menu);
    }

    /**
     * Verifica si un usuario tiene acceso a una vista especÃ­fica y retorna su ruta
     */
    public function tieneAcceso(int $idUsuario, string $viewKey)
    {
        // El dashboard es un caso especial fijo
        if ($viewKey === 'dashboard') {
            return [
                'success' => true,
                'path' => 'views/Dashboard/dashboard.view.php'
            ];
        }

        // 0. Verificar si el usuario es Desarrollador (Rol 1)
        $isDev = false;
        $checkSql = "SELECT idRolFK FROM usuarios WHERE idUsuario = :id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute(['id' => $idUsuario]);
        if ((int) $checkStmt->fetchColumn() === 1) {
            $isDev = true;
        }

        if ($isDev) {
            // Si es DEV, buscamos la ruta en el catÃ¡logo general sin mirar permisos
            $sql = "SELECT m.folder_name as modfolder, s.folder_name as subfolder, s.view_key
                    FROM modulo_sub_modulo msm 
                    JOIN sub_modulos s ON msm.idsubmodulofk = s.idsubmodulo
                    JOIN modulos m ON msm.idmodulofk = m.idmodulo
                    WHERE s.view_key = :view";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['view' => $viewKey]);
        } else {
            // Usuario normal: Validar contra usuario_permisos
            $sql = "SELECT m.folder_name as modfolder, s.folder_name as subfolder, s.view_key
                    FROM usuario_permisos p
                    JOIN modulo_sub_modulo msm ON p.idmodulosubmodulofk = msm.idmodulosubmodulo
                    JOIN sub_modulos s ON msm.idsubmodulofk = s.idsubmodulo
                    JOIN modulos m ON msm.idmodulofk = m.idmodulo
                    WHERE p.idusuariofk = :idUsuario 
                    AND s.view_key = :view
                    AND p.acceso = true";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'idUsuario' => $idUsuario,
                'view' => $viewKey
            ]);
        }

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            $path = "views/{$res['modfolder']}/{$res['subfolder']}/{$res['view_key']}.view.php";
            return [
                'success' => true,
                'path' => $path
            ];
        }

        return ['success' => false];
    }
}


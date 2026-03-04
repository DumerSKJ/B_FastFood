<?php
namespace App\Menu\Controllers;

use App\Menu\Services\MenuService;

class MenuController
{
    private $service;

    public function __construct(MenuService $service)
    {
        $this->service = $service;
    }

    public function getMenuRelaciones()
    {
        // Liberamos el bloqueo de sesión temprano ya que esta es una acción de solo lectura
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        try {
            $lista = $this->service->listarRelacionesMenu();
            $user = $_SESSION['user'] ?? [];
            $roleId = (int) ($user['idRolFK'] ?? $user['idrolfk'] ?? 0);

            return [
                'success' => true,
                'data' => $lista,
                'userRole' => $roleId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener el menÃº: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarMenu(array $data)
    {
        $user = $_SESSION['user'] ?? [];
        $roleId = (int) ($user['idRolFK'] ?? $user['idrolfk'] ?? 0);
        $success = $this->service->actualizarMenuCompleto($roleId, $data['data']);
        return ['success' => $success];
    }

    public function salvarModulo(array $data)
    {
        $user = $_SESSION['user'] ?? [];
        $roleId = (int) ($user['idRolFK'] ?? $user['idrolfk'] ?? 0);
        $id = (int) $data['idModulo'];
        $success = $this->service->guardarModulo($roleId, $id, $data['data']);
        return ['success' => $success];
    }

    public function salvarSubModulo(array $data)
    {
        $user = $_SESSION['user'] ?? [];
        $roleId = (int) ($user['idRolFK'] ?? $user['idrolfk'] ?? 0);
        $id = (int) $data['idSubModulo'];
        $success = $this->service->guardarSubModulo($roleId, $id, $data['data']);
        return ['success' => $success];
    }

    public function salvarRelacion(array $data)
    {
        $id = (int) $data['idModuloSubModulo'];
        $success = $this->service->guardarRelacion($id, $data['data']);
        return ['success' => $success];
    }

    public function getModulos()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return [
            'success' => true,
            'data' => $this->service->listarModulos()
        ];
    }

    public function crearModulo(array $data)
    {
        $user = $_SESSION['user'] ?? [];
        $roleId = (int) ($user['idRolFK'] ?? $user['idrolfk'] ?? 0);
        $success = $this->service->crearModuloSolo($roleId, $data['data']);
        return ['success' => $success];
    }

    public function crearSubModulo(array $data)
    {
        $user = $_SESSION['user'] ?? [];
        $roleId = (int) ($user['idRolFK'] ?? $user['idrolfk'] ?? 0);
        $success = $this->service->crearSubModuloRelacionado($roleId, $data['data']);
        return ['success' => $success];
    }

    public function crearMenu(array $data)
    {
        $user = $_SESSION['user'] ?? [];
        $roleId = (int) ($user['idRolFK'] ?? $user['idrolfk'] ?? 0);
        $success = $this->service->crearMenuCompleto($roleId, $data['data']);
        return ['success' => $success];
    }
}

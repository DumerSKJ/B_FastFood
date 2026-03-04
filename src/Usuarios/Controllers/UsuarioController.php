<?php
declare(strict_types=1);

namespace App\Usuarios\Controllers;

use App\Usuarios\Services\UsuarioService;

class UsuarioController
{
    public function __construct(private UsuarioService $service)
    {
    }

    public function getUsuarios(array $input = []): array
    {
        $roleId = (int) ($_SESSION['user']['idRolFK'] ?? 0);
        $userId = (int) ($_SESSION['user']['idUsuario'] ?? 0);

        $lista = $this->service->listarUsuarios();

        // Regla: Solo Super Admin (2) y Desarrollador (1) ven a todos.
// Otros roles solo se ven a sÃ­ mismos (si tienen acceso al mÃ³dulo).
        if ($roleId > 2) {
            $lista = array_filter($lista, function ($u) use ($userId) {
                return $u['idUsuario'] === $userId;
            });
            $lista = array_values($lista); // Reindexar
        }

        return [
            'success' => true,
            'data' => $lista
        ];
    }

    public function salvarUsuario(array $input = []): array
    {
        // En este sistema api.php pasa los datos en el argumento $input
        $data = $input['data'] ?? [];
        $id = isset($data['idUsuario']) ? (int) $data['idUsuario'] : 0;

        if ($id > 0) {
            // Si viene de un FormData enviado como JSON, los datos estÃ¡n en data
            $res = $this->service->actualizarUsuario($id, $data);
            $msg = $res ? "Usuario actualizado correctamente" : "Error al actualizar usuario (quizÃ¡s el nombre de usuario ya
existe)";
            return ['success' => $res, 'message' => $msg];
        }

        return ['success' => false, 'message' => "AcciÃ³n no permitida"];
    }

    public function getPermisos(array $input = []): array
    {
        $data = $input['data'] ?? [];
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $permisos = $this->service->getPermisos($id);

        return [
            'success' => true,
            'data' => $permisos
        ];
    }

    public function salvarPermisos(array $input = []): array
    {
        // El router api.php pasa todo el JSON decodificado en $input
// El JS envÃ­a { controller, action, data: { idUsuario, permisos } }
        $data = $input['data'] ?? [];

        $id = isset($data['idUsuario']) ? (int) $data['idUsuario'] : 0;
        $permisos = isset($data['permisos']) ? $data['permisos'] : [];

        if ($id <= 0) {
            return ['success' => false, 'message' => "ID de usuario no vÃ¡lido"];
        }

        $res = $this->service->salvarPermisos($id, $permisos);
        return [
            'success' => $res,
            'message' => $res ? "Permisos actualizados correctamente" : "Error al guardar permisos"
        ];
    }
}


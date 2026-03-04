<?php
declare(strict_types=1);

namespace App\Rol\Controllers;

use App\Rol\Services\RolService;

class RolController
{
    public function __construct(private RolService $service)
    {
    }

    public function getRoles(array $input = []): array
    {
        $lista = $this->service->listarRoles();
        return [
            'success' => true,
            'data' => $lista
        ];
    }

    public function salvarRol(array $input = []): array
    {
        $data = $input['data'] ?? [];
        $id = isset($data['idRol']) ? (int) $data['idRol'] : 0;

        if ($id > 0) {
            $res = $this->service->actualizarRol($id, $data);
            $msg = $res ? "Rol actualizado correctamente" : "Error al actualizar el rol";
        } else {
            $res = $this->service->crearRol($data);
            $msg = $res ? "Rol creado correctamente" : "Error al crear el rol";
        }

        return [
            'success' => $res,
            'message' => $msg
        ];
    }
}


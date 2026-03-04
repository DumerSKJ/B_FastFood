<?php
declare(strict_types=1);

namespace App\Usuarios\Services;

use App\Usuarios\Repositories\UsuarioRepository;

class UsuarioService
{
    public function __construct(private UsuarioRepository $repository)
    {
    }

    public function listarUsuarios(): array
    {
        return $this->repository->getListaCompleta();
    }

    public function actualizarUsuario(int $id, array $data): bool
    {
        if ($id <= 0)
            return false;

        // Verificar si el usuario estÃ¡ disponible si se cambia
        if (!empty($data['usuario'])) {
            if (!$this->repository->isUsernameAvailable($data['usuario'], $id)) {
                return false;
            }
        }

        return $this->repository->update($id, $data);
    }

    public function getPermisos(int $userId): array
    {
        return $this->repository->getPermisosUsuario($userId);
    }

    public function salvarPermisos(int $userId, array $permisos): bool
    {
        if ($userId <= 0)
            return false;
        return $this->repository->syncPermisos($userId, $permisos);
    }
}


<?php
declare(strict_types=1);

namespace App\Rol\Services;

use App\Rol\Repositories\RolRepository;

class RolService
{
    public function __construct(private RolRepository $repository)
    {
    }

    public function listarRoles(): array
    {
        return $this->repository->getRolesList();
    }

    public function crearRol(array $data): bool
    {
        if (empty($data['nombreRol']))
            return false;
        return $this->repository->create($data);
    }

    public function actualizarRol(int $id, array $data): bool
    {
        if (empty($data['nombreRol']) || $id <= 0)
            return false;
        return $this->repository->update($id, $data);
    }

}


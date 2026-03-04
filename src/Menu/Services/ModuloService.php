<?php
namespace App\Menu\Services;

use App\Menu\Repositories\ModuloRepository;

class ModuloService
{
    private $repository;

    public function __construct(ModuloRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerMenu(int $idUsuario): array
    {
        return $this->repository->getMenuUsuario($idUsuario);
    }

    public function verificarAcceso(int $idUsuario, string $viewKey): bool
    {
        $res = $this->repository->tieneAcceso($idUsuario, $viewKey);
        return $res['success'] ?? false;
    }

    public function checkAccessConPath(int $idUsuario, string $viewKey): array
    {
        return $this->repository->tieneAcceso($idUsuario, $viewKey);
    }
}


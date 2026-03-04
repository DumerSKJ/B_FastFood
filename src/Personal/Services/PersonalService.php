<?php
declare(strict_types=1);

namespace App\Personal\Services;

use App\Personal\Repositories\PersonalRepository;
use App\Usuarios\Repositories\UsuarioRepository;

class PersonalService
{
    public function __construct(
        private PersonalRepository $repository,
        private UsuarioRepository $usuarioRepo
    ) {
    }

    public function listarPersonal(): array
    {
        return $this->repository->getPersonalList();
    }

    public function crearPersonal(array $data): bool
    {
        if (empty($data['nombres']) || empty($data['apellidos'])) {
            throw new \Exception("Nombres y apellidos son obligatorios");
        }

        // 0. ValidaciÃ³n de datos de usuario (OBLIGATORIO)
        if (empty($data['usuario']) || empty($data['clave']) || empty($data['idRolFK'])) {
            throw new \Exception("Es obligatorio asignar un Usuario, ContraseÃ±a y Rol al nuevo personal.");
        }

        if (!$this->usuarioRepo->isUsernameAvailable($data['usuario'])) {
            throw new \Exception("El nombre de usuario '{$data['usuario']}' ya estÃ¡ ocupado");
        }

        // 1. Crear el registro de Personal
        $idPersonal = $this->repository->create($data);
        if ($idPersonal <= 0)
            return false;

        // 2. Crear la cuenta de usuario vinculada
        $userData = [
            'idRolFK' => (int) $data['idRolFK'],
            'idPersonalFK' => $idPersonal,
            'usuario' => $data['usuario'],
            'clave' => $data['clave']
        ];
        $idUsuario = $this->usuarioRepo->create($userData);

        // 3. Aplicar permisos predeterminados del rol (si existen)
        if ($idUsuario > 0) {
            $this->usuarioRepo->aplicarPermisosDeRol((int) $data['idRolFK'], $idUsuario);
        }

        return true;
    }

    public function actualizarPersonal(int $id, array $data): bool
    {
        if ($id <= 0 || empty($data['nombres']) || empty($data['apellidos']))
            return false;
        return $this->repository->update($id, $data);
    }

}


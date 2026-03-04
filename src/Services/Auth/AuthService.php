<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\Auth\AuthRepository;
use Exception;

class AuthService
{
    public function __construct(private AuthRepository $repository)
    {
    }

    /**
     * Autentica al usuario
     */
    public function authenticate(string $password, string $usuario): array
    {
        try {
            $user = $this->repository->getUserByUsername($usuario);

            if (!$user || !password_verify($password, $user['clave'])) {
                return ['success' => false, 'message' => 'Credenciales inválidas'];
            }

            // Verificar si el usuario está activo
            if ($user['estado'] !== 'Activo') {
                return ['success' => false, 'message' => 'Usuario inactivo. Contacte al administrador.'];
            }

            // Datos para la sesión y el frontend
            $infoUsuario = [
                'idUsuario' => $user['idusuario'],
                'idRolFK' => (int) $user['idrolfk'],
                'usuario' => $user['usuario'],
                'nombreCompleto' => $user['nombres'] . ' ' . $user['apellidos'],
                'rol' => $user['rol']
            ];

            return [
                'success' => true,
                'message' => 'Bienvenido ' . $infoUsuario['nombreCompleto'],
                'user' => $infoUsuario
            ];
        } catch (Exception $e) {
            throw new Exception("Error en servicio de login: " . $e->getMessage());
        }
    }
}
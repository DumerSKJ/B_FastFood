<?php

namespace App\Controllers\Auth;

use App\Services\Auth\AuthService;
use App\DatabaseConfig;
use \Exception;

class AuthController
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Autentica al usuario
     */
    public function authenticate($input)
    {
        $usuario = $input['usuario'] ?? '';
        $password = $input['password'] ?? '';

        $authData = $this->authService->authenticate($password, $usuario);

        if (!$authData['success']) {
            return ['success' => false, 'error' => $authData['message']];
        }

        // Definir nombre de sesión único y configurar seguridad dinámica
        if (session_status() === PHP_SESSION_NONE) {
            session_name('SESSION_PROYECTO1');

            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

            session_set_cookie_params(
                DatabaseConfig::getSessionDuration(),
                '/',
                '',
                $isSecure,
                true
            );

            session_start();
            session_regenerate_id(true);
        }

        $_SESSION['user'] = $authData['user'];
        $_SESSION['authenticated'] = true;
        $_SESSION['auth_time'] = time();

        // Aseguramos que el array devuelto tenga TODO lo necesario para que el Frontend lo guarde
        return [
            'success' => true,
            'data' => [
                'authenticated' => true,
                'message' => $authData['message'],
                'user' => array_merge($authData['user'], [
                    'authenticated' => true,
                    'auth_time' => time()
                ]),
                'session_expires' => time() + DatabaseConfig::getSessionDuration()
            ]
        ];
    }

    /**
     * Cierra sesión
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('SESSION_PROYECTO1');
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
            session_set_cookie_params(0, '/', '', $isSecure, true);
            session_start();
        }
        session_destroy();
        return ['success' => true, 'data' => ['message' => 'Sesión cerrada exitosamente']];
    }

    /**
     * Verifica si la sesión sigue activa
     */
    public function checkSession()
    {
        $sessionCheck = $this->isAuthenticated();
        if ($sessionCheck['valid']) {
            return [
                'success' => true,
                'data' => [
                    'authenticated' => true,
                    'time_remaining' => $sessionCheck['time_remaining'],
                    'expires_at' => $sessionCheck['expires_at']
                ]
            ];
        }
        return ['success' => false, 'error' => $sessionCheck['message']];
    }

    /**
     * Comprueba si el usuario está autenticado
     */
    public function isAuthenticated()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('SESSION_PROYECTO1');
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
            session_set_cookie_params(0, '/', '', $isSecure, true);
            session_start();
        }

        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            return ['valid' => false, 'message' => 'Inicie sesión para continuar'];
        }

        $timeElapsed = time() - $_SESSION['auth_time'];
        $timeRemaining = DatabaseConfig::getSessionDuration() - $timeElapsed;

        if ($timeElapsed > DatabaseConfig::getSessionDuration()) {
            session_destroy();
            return ['valid' => false, 'message' => 'Sesión expirada. Debe autenticarse nuevamente.'];
        }

        return [
            'user' => $_SESSION['user'] ?? null,
            'valid' => true,
            'time_remaining' => $timeRemaining,
            'expires_at' => $_SESSION['auth_time'] + DatabaseConfig::getSessionDuration()
        ];
    }
}

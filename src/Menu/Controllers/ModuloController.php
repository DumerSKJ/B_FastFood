<?php
namespace App\Menu\Controllers;

use App\Menu\Services\ModuloService;

class ModuloController
{
    private $service;

    public function __construct(ModuloService $service)
    {
        $this->service = $service;
    }

    public function getMenuUsuario($input)
    {
        $idUsuario = (int) ($input['userId'] ?? 0);
        if (!$idUsuario)
            return ['success' => false, 'message' => 'ID de usuario no proporcionado'];

        return [
            'success' => true,
            'data' => $this->service->obtenerMenu($idUsuario)
        ];
    }

    public function checkAccess($input)
    {
        $idUsuario = (int) ($input['userId'] ?? 0);
        $view = $input['view'] ?? '';

        if (!$idUsuario || !$view) {
            return ['success' => false, 'message' => 'Datos insuficientes para validar acceso'];
        }

        // El servicio retornar solo bool, pero el repositorio retorna el array completo.
        // Vamos a usar directamente el repositorio o actualizar el servicio.
        // Para no romper la cadena, actualizaremos el controlador para llamar al repo o el servicio.

        // El repositorio directamente maneja la lÃ³gica de path
        return $this->service->checkAccessConPath($idUsuario, $view);
    }
}


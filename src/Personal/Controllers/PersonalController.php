<?php
declare(strict_types=1);

namespace App\Personal\Controllers;

use App\Personal\Services\PersonalService;
use App\Extensions\ConsultaCredencialesSunat;

class PersonalController
{
    private ConsultaCredencialesSunat $sunatService;

    public function __construct(private PersonalService $service)
    {
        $this->sunatService = new ConsultaCredencialesSunat();
    }

    public function getPersonal(array $input = []): array
    {
        $lista = $this->service->listarPersonal();
        return [
            'success' => true,
            'data' => $lista
        ];
    }

    public function salvarPersonal(array $input = []): array
    {
        $data = $input['data'] ?? [];
        $id = isset($data['idPersonal']) ? (int) $data['idPersonal'] : 0;

        try {
            if ($id > 0) {
                $res = $this->service->actualizarPersonal($id, $data);
                $msg = $res ? "Registro de personal actualizado" : "Error al actualizar";
            } else {
                $res = $this->service->crearPersonal($data);
                $msg = $res ? "Registro de personal creado correctamente" : "Error al crear registro";
            }

            return [
                'success' => $res,
                'message' => $msg
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Consulta DNI usando el servicio externo de RENIEC/SUNAT
     */
    public function consultarDni(array $input = []): mixed
    {
        $dni = $input['data']['dni'] ?? '';
        return $this->sunatService->buscarPorDni($dni);
    }
}


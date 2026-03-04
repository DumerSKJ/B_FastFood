<?php
declare(strict_types=1);

namespace App\Extensions;

/**
 * Servicio de Consulta de Credenciales SUNAT
 * Consume API externa de RENIEC/SUNAT para obtener datos de personas (DNI) y empresas (RUC)
 */
class ConsultaCredencialesSunat
{
    private string $apiUrl;
    private string $token;

    public function __construct()
    {
        $this->apiUrl = $_ENV['URL_API'] ?? '';
        $this->token = $_ENV['TOKEN_API'] ?? '';
    }

    /**
     * Busca información de una persona por DNI
     * @param string $dni - DNI de 8 dígitos
     * @return array - ['success' => bool, 'data' => array|null, 'message' => string]
     */
    public function buscarPorDni(string $dni): array
    {
        // Validación de DNI
        if (strlen($dni) !== 8 || !is_numeric($dni)) {
            return ['success' => false, 'message' => 'DNI inválido (debe tener 8 dígitos)'];
        }

        return $this->consultarApi('/dni?numero=' . $dni, 'dni');
    }

    /**
     * Busca información de una empresa por RUC
     * @param string $ruc - RUC de 11 dígitos
     * @return array - ['success' => bool, 'data' => array|null, 'message' => string]
     */
    public function buscarPorRuc(string $ruc): array
    {
        // Validación de RUC
        if (strlen($ruc) !== 11 || !is_numeric($ruc)) {
            return ['success' => false, 'message' => 'RUC inválido (debe tener 11 dígitos)'];
        }

        return $this->consultarApi('/ruc?numero=' . $ruc, 'ruc');
    }

    /**
     * Busca por DNI o RUC automáticamente según la longitud
     * @param string $credencial - DNI (8 dígitos) o RUC (11 dígitos)
     * @return array - ['success' => bool, 'data' => array|null, 'message' => string, 'tipo' => 'dni'|'ruc']
     */
    public function buscarPorDniORuc(string $credencial): array
    {
        $credencial = trim($credencial);

        if (strlen($credencial) === 8) {
            $result = $this->buscarPorDni($credencial);
            $result['tipo'] = 'dni';
            return $result;
        } elseif (strlen($credencial) === 11) {
            $result = $this->buscarPorRuc($credencial);
            $result['tipo'] = 'ruc';
            return $result;
        } else {
            return [
                'success' => false,
                'message' => 'Credencial inválida (debe ser DNI de 8 dígitos o RUC de 11 dígitos)',
                'tipo' => null
            ];
        }
    }

    /**
     * Método privado para realizar la consulta a la API
     * @param string $endpoint - Endpoint de la API (/dni?numero=... o /ruc?numero=...)
     * @param string $tipo - 'dni' o 'ruc'
     * @return array
     */
    private function consultarApi(string $endpoint, string $tipo): array
    {
        // Verificar configuración
        if (empty($this->apiUrl) || empty($this->token)) {
            return ['success' => false, 'message' => 'Servicio de consulta no configurado'];
        }

        $url = rtrim($this->apiUrl, '/') . $endpoint;

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->token,
                'Referer: https://apis.net.pe/consulta-dni-api',
                'User-Agent: apis-client/1.0.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);

                if ($tipo === 'dni') {
                    return [
                        'success' => true,
                        'data' => [
                            'nombres' => $data['nombres'] ?? '',
                            'apellidos' => trim(($data['apellidoPaterno'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? '')),
                            'dni' => $data['numeroDocumento'] ?? ''
                        ]
                    ];
                } else { // ruc
                    return [
                        'success' => true,
                        'data' => [
                            'razonSocial' => $data['nombre'] ?? $data['razonSocial'] ?? '',
                            'ruc' => $data['numeroDocumento'] ?? $data['ruc'] ?? '',
                            'estado' => $data['estado'] ?? '',
                            'condicion' => $data['condicion'] ?? '',
                            'direccion' => $data['direccion'] ?? ''
                        ]
                    ];
                }
            } else {
                $tipoTexto = $tipo === 'dni' ? 'DNI' : 'RUC';
                return ['success' => false, 'message' => "No se encontró información para este $tipoTexto"];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error de conexión con servicio externo: ' . $e->getMessage()];
        }
    }
}

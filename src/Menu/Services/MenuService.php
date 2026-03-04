<?php
namespace App\Menu\Services;

use App\Menu\Repositories\MenuRepository;

class MenuService
{
    private $repository;

    public function __construct(MenuRepository $repository)
    {
        $this->repository = $repository;
    }

    // --- MÃ©todos de Consulta ---

    public function listarRelacionesMenu(): array
    {
        return $this->repository->getListaMenuRelaciones();
    }

    public function listarModulos(): array
    {
        return $this->repository->getModulos();
    }

    // --- MÃ©todos de ActualizaciÃ³n ---

    public function actualizarMenuCompleto(int $roleId, array $data): bool
    {
        try {
            // 1. Datos del MÃ³dulo
            $modData = [
                'nombreModulo' => $data['nombreModulo'],
                'icono' => $data['modIcono'],
                'orden' => $data['modOrden']
            ];
            if ($roleId === 1 && isset($data['modFolder'])) {
                $modData['folder_name'] = $data['modFolder'];
            }
            $this->repository->updateModulo((int)$data['idModulo'], $modData);

            // 2. Datos del SubmÃ³dulo
            $subData = [
                'nombreSubModulo' => $data['nombreSubModulo'],
                'icono' => $data['subIcono']
            ];
            if ($roleId === 1) {
                if (isset($data['subFolder'])) $subData['folder_name'] = $data['subFolder'];
                if (isset($data['view_key'])) $subData['view_key'] = $data['view_key'];
            }
            $this->repository->updateSubModulo((int)$data['idSubModulo'], $subData);

            // 3. RelaciÃ³n (Permite cambiar de MÃ³dulo Padre)
            $relData = ['orden' => $data['relOrden']];
            if (isset($data['idModuloPadre']) && (int)$data['idModuloPadre'] > 0) {
                $relData['idModuloFK'] = (int)$data['idModuloPadre'];
            }
            $this->repository->updateRelacion((int)$data['idModuloSubModulo'], $relData);

            // 4. Re-ordenamiento Masivo (Si se enviÃ³ el array de orden)
            if (isset($data['sortOrder']) && is_array($data['sortOrder'])) {
                foreach ($data['sortOrder'] as $sortItem) {
                    if (isset($sortItem['id']) && isset($sortItem['orden'])) {
                        $this->repository->updateRelacion((int)$sortItem['id'], ['orden' => (int)$sortItem['orden']]);
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function guardarModulo(int $roleId, int $id, array $data): bool
    {
        // Si no es desarrollador, restringir campos sensibles
        if ($roleId !== 1) {
            unset($data['folder_name']);
        }
        return $this->repository->updateModulo($id, $data);
    }

    public function guardarSubModulo(int $roleId, int $id, array $data): bool
    {
        // Si no es desarrollador, restringir campos sensibles
        if ($roleId !== 1) {
            unset($data['folder_name']);
            unset($data['view_key']);
        }
        return $this->repository->updateSubModulo($id, $data);
    }

    public function guardarRelacion(int $id, array $data): bool
    {
        return $this->repository->updateRelacion($id, $data);
    }

    public function crearModuloSolo(int $roleId, array $data): bool
    {
        if ($roleId !== 1) return false;
        $id = $this->repository->createModulo([
            'nombreModulo' => $data['nombreModulo'],
            'folder_name' => $data['modFolder'],
            'icono' => $data['modIcono'],
            'orden' => $data['modOrden']
        ]);
        return $id > 0;
    }

    public function crearSubModuloRelacionado(int $roleId, array $data): bool
    {
        if ($roleId !== 1) return false;
        
        // 1. Crear submÃ³dulo
        $idSubModulo = $this->repository->createSubModulo([
            'nombreSubModulo' => $data['nombreSubModulo'],
            'folder_name' => $data['subFolder'],
            'view_key' => $data['view_key'],
            'icono' => $data['subIcono']
        ]);

        // 2. Vincular a mÃ³dulo existente
        return $this->repository->linkModuloSubModulo((int)$data['idModuloPadre'], $idSubModulo, (int)$data['relOrden']);
    }

    /** @deprecated Usar crearModuloSolo o crearSubModuloRelacionado */
    public function crearMenuCompleto(int $roleId, array $data): bool
    {
        if ($roleId !== 1) return false;

        // 1. Crear o seleccionar mÃ³dulo
        $idModulo = $data['idModulo'] ?? null;
        if (!$idModulo) {
            $idModulo = $this->repository->createModulo([
                'nombreModulo' => $data['nombreModulo'],
                'folder_name' => $data['modFolder'],
                'icono' => $data['modIcono'],
                'orden' => $data['modOrden']
            ]);
        }

        // 2. Crear submÃ³dulo
        $idSubModulo = $this->repository->createSubModulo([
            'nombreSubModulo' => $data['nombreSubModulo'],
            'folder_name' => $data['subFolder'],
            'view_key' => $data['view_key'],
            'icono' => $data['subIcono']
        ]);

        // 3. Vincular
        return $this->repository->linkModuloSubModulo($idModulo, $idSubModulo, $data['relOrden']);
    }
}


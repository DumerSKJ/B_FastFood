<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\DatabaseManager;
use App\Controllers\Shared\CacheController;
use App\Controllers\Auth\AuthController;
use App\Services\Auth\AuthService;
use App\Repositories\Auth\AuthRepository;


// Módulos Refactorizados
use App\Usuarios\Controllers\UsuarioController;
use App\Usuarios\Services\UsuarioService;
use App\Usuarios\Repositories\UsuarioRepository;

use App\Personal\Controllers\PersonalController;
use App\Personal\Services\PersonalService;
use App\Personal\Repositories\PersonalRepository;

use App\Rol\Controllers\RolController;
use App\Rol\Services\RolService;
use App\Rol\Repositories\RolRepository;

use App\Menu\Controllers\MenuController;
use App\Menu\Services\MenuService;
use App\Menu\Repositories\MenuRepository;

use App\Menu\Controllers\ModuloController;
use App\Menu\Services\ModuloService;
use App\Menu\Repositories\ModuloRepository;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Configuración de sesión unificada
if (session_status() === PHP_SESSION_NONE) {
    session_name('SESSION_PROYECTO1');
    session_start();
}

$dbManager = new DatabaseManager();

// Registro de dependencias usando match (PHP 8.0+)
// En lugar de un array, resolvemos bajo demanda.
function resolveController(string $key, DatabaseManager $dbManager)
{
    if ($key === 'cache')
        return new CacheController();
    if ($key === 'auth')
        return new AuthController(new AuthService(new AuthRepository($dbManager)));
    if ($key === 'usuario')
        return new UsuarioController(new UsuarioService(new UsuarioRepository($dbManager)));
    if ($key === 'personal')
        return new PersonalController(new PersonalService(new PersonalRepository($dbManager), new UsuarioRepository($dbManager)));
    if ($key === 'rol')
        return new RolController(new RolService(new RolRepository($dbManager)));
    if ($key === 'modulo')
        return new ModuloController(new ModuloService(new ModuloRepository($dbManager)));
    if ($key === 'menu')
        return new MenuController(new MenuService(new MenuRepository($dbManager)));
    return null;
}

// Procesamiento de la petición
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Puente de Sesión (Session Bridge)
// Si el Proxy (Frontend) envió datos de sesión, los inyectamos en la sesión local del backend
if (isset($input['_session_data'])) {
    $sessionBridge = $input['_session_data'];
    $providedToken = $sessionBridge['bridge_token'] ?? '';
    $expectedToken = $_ENV['SESSION_BRIDGE_TOKEN'] ?? '';

    // Solo hidratamos la sesión si el token coincide exactamente
    if (!empty($expectedToken) && $providedToken === $expectedToken) {
        $_SESSION['authenticated'] = $sessionBridge['authenticated'] ?? false;
        $_SESSION['user'] = $sessionBridge['user'] ?? [];
        $_SESSION['auth_time'] = $sessionBridge['auth_time'] ?? time();
    }
}

$controllerKey = $input['controller'] ?? $_GET['controller'] ?? '';
$action = $input['action'] ?? $_GET['action'] ?? '';

try {
    $controller = resolveController($controllerKey, $dbManager);

    if ($controller) {
        if (method_exists($controller, $action)) {
            $response = $controller->$action($input);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "Acción '$action' no encontrada en '$controllerKey'"]);
        }
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Controlador '$controllerKey' no encontrado"]);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Error interno: " . $e->getMessage()]);
}

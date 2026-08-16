<?php
// Serve static files when using PHP built-in server
if (PHP_SAPI === 'cli-server') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $candidate = realpath(__DIR__ . $reqPath);
    $webRoot = realpath(__DIR__);
    $webPrefix = $webRoot . DIRECTORY_SEPARATOR;
    $file = ($candidate && $webRoot && is_file($candidate)
        && (strpos($candidate, $webPrefix) === 0 || $candidate === $webRoot))
        ? $candidate
        : null;
    if ($file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ttf'  => 'font/ttf',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        return false;
    }
}

// Session hardening
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly'  => true,
    'samesite' => 'Strict',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:; connect-src 'self' https://cdn.jsdelivr.net; font-src 'self' data: https://cdn.jsdelivr.net");

require_once __DIR__ . '/../app/config/app.php';
require_once BASE_PATH . '/app/helpers/Session.php';
require_once BASE_PATH . '/app/helpers/Response.php';
require_once BASE_PATH . '/app/middleware/Csrf.php';
require_once BASE_PATH . '/app/middleware/Auth.php';

// ============================================================
// Manejador global de excepciones
// Captura cualquier error no controlado y muestra un mensaje
// amigable al usuario en vez de un stack trace de PHP.
// ============================================================
set_exception_handler(function ($e) {
    // Loguear siempre el detalle tecnico
    error_log('[' . date('Y-m-d H:i:s') . '] ' . get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());

    $isDebug = (getenv('APP_DEBUG') === 'true') || (($_ENV['APP_DEBUG'] ?? '') === 'true');
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
              && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    $userMsg = 'Ocurrio un error inesperado. Por favor intenta de nuevo o contacta al administrador.';

    http_response_code(500);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $userMsg,
            'debug'   => $isDebug ? $e->getMessage() : null,
        ]);
        exit;
    }

    // Pagina HTML amigable
    $debugBlock = $isDebug
        ? '<pre style="text-align:left;background:#f8f8f8;padding:16px;border-radius:8px;font-size:12px;overflow:auto;max-height:300px;margin-top:24px;">'
            . htmlspecialchars($e->getMessage() . "\n\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString())
            . '</pre>'
        : '';

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<title>Error - Sistema</title>'
        . '<style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#F0F2F5;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;}'
        . '.card{background:white;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,0.1);max-width:520px;width:100%;padding:40px;text-align:center;}'
        . '.icon{font-size:48px;color:#E74C3C;margin-bottom:16px;}'
        . 'h1{color:#2C3E50;font-size:22px;margin:0 0 12px;}'
        . 'p{color:#5D6D7E;font-size:15px;line-height:1.5;margin:0 0 24px;}'
        . '.btn{display:inline-block;background:#2C3E50;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;}'
        . '.btn:hover{background:#1A252F;}</style></head>'
        . '<body><div class="card">'
        . '<div class="icon">&#9888;</div>'
        . '<h1>Algo salio mal</h1>'
        . '<p>' . htmlspecialchars($userMsg) . '</p>'
        . '<a href="' . BASE_URL . '/" class="btn">Volver al inicio</a>'
        . $debugBlock
        . '</div></body></html>';
    exit;
});

// Resolve route from URL path (works with both built-in server and Apache)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(BASE_URL, '/');
if ($basePath && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
// Ruta por defecto segun rol: admin va al dashboard, operador al panel touch
$defaultRoute = (Auth::check() && !Auth::isAdmin()) ? 'solicitudes/nueva' : 'dashboard';
$route = trim($uri, '/') ?: $defaultRoute;

// Also support ?route= for backwards compatibility
if (isset($_GET['route'])) {
    $route = trim($_GET['route'], '/');
}

$method = $_SERVER['REQUEST_METHOD'];

// Rutas publicas (no requieren login)
$publicRoutes = [
    'GET:login',
    'POST:login',
];

// Rutas restringidas a admin
$adminRoutes = [
    'GET:dashboard',
    'GET:api/dashboard/resumen',
    'GET:api/dashboard/heartbeat',
    'GET:personas',
    'POST:personas/actualizar',
    'POST:personas/eliminar',
    'POST:personas/habilitar',
    'GET:personas/importar',
    'GET:personas/importar/preview',
    'GET:personas/importar/cancelar',
    'POST:personas/importar/subir',
    'POST:personas/importar/confirmar',
    'GET:personas/plantilla',
    'GET:solicitudes',
    'GET:solicitudes/ver',
    'POST:solicitudes/cambiar-estado',
    'POST:solicitudes/eliminar',
    'POST:api/sedes/crear',
    'POST:api/sedes/actualizar',
    'POST:api/sedes/eliminar',
    'POST:api/motivos/crear',
    'POST:api/motivos/actualizar',
    'POST:api/motivos/eliminar',
    'GET:api/estados',
    'POST:api/estados/crear',
    'POST:api/estados/actualizar',
    'POST:api/estados/eliminar',
    'GET:reportes',
    'POST:reportes/generar-pdf',
    'GET:cupos',
    'POST:cupos/actualizar',
    'GET:configuracion',
    'POST:configuracion/guardar',
    'POST:admin/usuarios/cambiar-password',
];
// Las rutas no listadas en $publicRoutes ni en $adminRoutes solo requieren login
// (cualquier rol). Esto incluye: solicitudes/nueva, personas/buscar, personas/crear,
// api/sedes, api/motivos, etc. — todo lo que el operador necesita.

$routes = [
    // Auth
    'GET:login'                 => ['AuthController', 'loginForm'],
    'POST:login'                => ['AuthController', 'login'],
    'POST:logout'               => ['AuthController', 'logout'],
    'POST:perfil/password'      => ['AuthController', 'cambiarPassword'],

    // Dashboard
    'GET:dashboard'             => ['DashboardController', 'index'],
    'GET:api/dashboard/resumen'   => ['DashboardController', 'resumen'],
    'GET:api/dashboard/heartbeat' => ['DashboardController', 'heartbeat'],

    // Personas
    'GET:personas'              => ['PersonaController', 'index'],
    'GET:personas/buscar'       => ['PersonaController', 'buscar'],
    'POST:personas/crear'       => ['PersonaController', 'crear'],
    'POST:personas/actualizar'  => ['PersonaController', 'actualizar'],
    'POST:personas/eliminar'    => ['PersonaController', 'eliminar'],
    'POST:personas/habilitar'   => ['PersonaController', 'habilitar'],
    'GET:personas/importar'             => ['PersonaController', 'importar'],
    'POST:personas/importar/subir'      => ['PersonaController', 'subirMaestro'],
    'GET:personas/importar/preview'     => ['PersonaController', 'previewMaestro'],
    'POST:personas/importar/confirmar'  => ['PersonaController', 'confirmarImport'],
    'GET:personas/importar/cancelar'    => ['PersonaController', 'cancelarImport'],
    'GET:personas/plantilla'            => ['PersonaController', 'descargarPlantilla'],

    // Solicitudes
    'GET:solicitudes'                => ['SolicitudController', 'listar'],
    'POST:solicitudes/crear'         => ['SolicitudController', 'crear'],
    'GET:solicitudes/nueva'          => ['SolicitudController', 'formTouch'],
    'POST:solicitudes/nueva'         => ['SolicitudController', 'crearTouch'],
    'GET:solicitudes/buscar-persona'  => ['SolicitudController', 'buscarPersona'],
    'POST:solicitudes/crear-persona'  => ['SolicitudController', 'crearPersona'],
    'GET:solicitudes/ver'            => ['SolicitudController', 'ver'],
    'POST:solicitudes/cambiar-estado'=> ['SolicitudController', 'cambiarEstado'],
    'POST:solicitudes/eliminar'      => ['SolicitudController', 'eliminar'],

    // Catálogos API - Sedes
    'GET:api/sedes'             => ['SedeController', 'listarActivas'],
    'POST:api/sedes/crear'      => ['SedeController', 'crear'],
    'POST:api/sedes/actualizar' => ['SedeController', 'actualizar'],
    'POST:api/sedes/eliminar'   => ['SedeController', 'eliminar'],

    // Catálogos API - Motivos de Ramo
    'GET:api/motivos'               => ['MotivoRamoController', 'listar'],
    'POST:api/motivos/crear'        => ['MotivoRamoController', 'crear'],
    'POST:api/motivos/actualizar'   => ['MotivoRamoController', 'actualizar'],
    'POST:api/motivos/eliminar'     => ['MotivoRamoController', 'eliminar'],

    // Catálogos API - Estados de Solicitud
    'GET:api/estados'               => ['EstadoSolicitudController', 'listar'],
    'POST:api/estados/crear'        => ['EstadoSolicitudController', 'crear'],
    'POST:api/estados/actualizar'   => ['EstadoSolicitudController', 'actualizar'],
    'POST:api/estados/eliminar'     => ['EstadoSolicitudController', 'eliminar'],

    // Reportes (ahora vive dentro de solicitudes, pero se mantienen rutas POST)
    'GET:reportes'              => ['ReporteController', 'index'],
    'POST:reportes/generar-pdf' => ['ReporteController', 'generarPdf'],

    // Cupos (redirect al tab, pero se mantiene POST)
    'GET:cupos'             => ['CupoController', 'index'],
    'POST:cupos/actualizar' => ['CupoController', 'actualizar'],
    'POST:api/cupos/verificar' => ['CupoController', 'verificar'],

    // Configuración
    'GET:configuracion'         => ['ConfigController', 'index'],
    'POST:configuracion/guardar'=> ['ConfigController', 'guardar'],

    // Admin: cambiar contraseña de cualquier usuario
    'POST:admin/usuarios/cambiar-password' => ['AuthController', 'cambiarPasswordAdmin'],
];

$routeKey = $method . ':' . $route;

// Share the resolved route with views (for sidebar active state, etc.)
$GLOBALS['current_route'] = $route;

// Aplicar middleware de autenticacion / autorizacion
if (!in_array($routeKey, $publicRoutes, true)) {
    if (in_array($routeKey, $adminRoutes, true)) {
        Auth::requireRol('admin');
    } else {
        Auth::guard();
    }
}

if (isset($routes[$routeKey])) {
    [$controllerFile, $action] = $routes[$routeKey];
    require_once BASE_PATH . '/app/controllers/' . $controllerFile . '.php';
    $controller = new $controllerFile();
    $controller->$action();
} else {
    http_response_code(404);
    if (strpos($route, 'api/') === 0) {
        Response::error('Ruta no encontrada', 404);
    } else {
        $pageTitle = 'No encontrado';
        $content = '<div class="text-center" style="padding:80px 0"><h1>404</h1><p>Página no encontrada</p><a href="' . BASE_URL . '/dashboard" class="btn btn-primary">Ir al inicio</a></div>';
        require BASE_PATH . '/app/views/layouts/main.php';
    }
}

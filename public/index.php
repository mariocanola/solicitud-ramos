<?php
// Serve static files when using PHP built-in server
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:");

require_once __DIR__ . '/../app/config/app.php';
require_once BASE_PATH . '/app/helpers/Session.php';
require_once BASE_PATH . '/app/helpers/Response.php';
require_once BASE_PATH . '/app/middleware/Csrf.php';
require_once BASE_PATH . '/app/middleware/Auth.php';

// Resolve route from URL path (works with both built-in server and Apache)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(BASE_URL, '/');
if ($basePath && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
$route = trim($uri, '/') ?: 'dashboard';

// Also support ?route= for backwards compatibility
if (isset($_GET['route'])) {
    $route = trim($_GET['route'], '/');
}

$method = $_SERVER['REQUEST_METHOD'];

// Public routes (no auth required)
$publicRoutes = [
    'GET:login'  => ['AuthController', 'loginForm'],
    'POST:login' => ['AuthController', 'login'],
    'GET:logout' => ['AuthController', 'logout'],
];

$publicRouteKey = $method . ':' . $route;
if (isset($publicRoutes[$publicRouteKey])) {
    [$controllerFile, $action] = $publicRoutes[$publicRouteKey];
    require_once BASE_PATH . '/app/controllers/' . $controllerFile . '.php';
    $controller = new $controllerFile();
    $controller->$action();
    exit;
}

// Auth guard - all other routes require authentication
Auth::guard();

$routes = [
    // Dashboard
    'GET:dashboard'             => ['DashboardController', 'index'],
    'GET:api/dashboard/resumen' => ['DashboardController', 'resumen'],

    // Personas
    'GET:personas'              => ['PersonaController', 'index'],
    'GET:personas/buscar'       => ['PersonaController', 'buscar'],
    'POST:personas/crear'       => ['PersonaController', 'crear'],
    'POST:personas/actualizar'  => ['PersonaController', 'actualizar'],
    'POST:personas/eliminar'    => ['PersonaController', 'eliminar'],

    // Solicitudes
    'GET:solicitudes'                => ['SolicitudController', 'listar'],
    'GET:solicitudes/crear'          => ['SolicitudController', 'formCrear'],
    'POST:solicitudes/crear'         => ['SolicitudController', 'crear'],
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
    'POST:reportes/enviar-correo'=> ['ReporteController', 'enviarCorreo'],

    // Cupos (redirect al tab, pero se mantiene POST)
    'GET:cupos'             => ['CupoController', 'index'],
    'POST:cupos/actualizar' => ['CupoController', 'actualizar'],

    // Configuración
    'GET:configuracion'         => ['ConfigController', 'index'],
    'POST:configuracion/guardar'=> ['ConfigController', 'guardar'],
];

$routeKey = $method . ':' . $route;

// Share the resolved route with views (for sidebar active state, etc.)
$GLOBALS['current_route'] = $route;

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

<?php
require_once BASE_PATH . '/app/models/Usuario.php';

class Auth
{
    public static function check()
    {
        return !empty($_SESSION['usuario']);
    }

    public static function user()
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function id()
    {
        return $_SESSION['usuario']['id'] ?? null;
    }

    public static function rol()
    {
        return $_SESSION['usuario']['rol'] ?? null;
    }

    public static function isAdmin()
    {
        return self::rol() === 'admin';
    }

    public static function guard()
    {
        if (!self::check()) {
            if (self::isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No autenticado']);
                exit;
            }
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireRol($roles)
    {
        self::guard();
        $roles = (array)$roles;
        if (!in_array(self::rol(), $roles, true)) {
            if (self::isAjax()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }
            http_response_code(403);
            echo '<div style="padding:80px;text-align:center"><h1>403</h1><p>No tienes permisos para acceder a esta seccion.</p><a href="' . BASE_URL . '/dashboard">Volver</a></div>';
            exit;
        }
    }

    public static function login($username, $password)
    {
        try {
            $userModel = new Usuario();
            $user = $userModel->buscarPorUsername($username);
        } catch (Throwable $e) {
            error_log('[Auth::login] Error al consultar usuario: ' . $e->getMessage());
            // Re-lanzamos para que el manejador global muestre el mensaje amigable
            throw $e;
        }

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'       => (int)$user['id'],
            'username' => $user['username'],
            'nombre'   => $user['nombre'],
            'rol'      => $user['rol'],
        ];
        $_SESSION['auth_time'] = time();
        try {
            $userModel->registrarLogin($user['id']);
        } catch (Throwable $e) {
            error_log('[Auth::login] No se pudo registrar ultimo_login: ' . $e->getMessage());
        }
        return true;
    }

    public static function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    private static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

<?php
// Authentication Middleware

class Auth
{
    public static function check()
    {
        return !empty($_SESSION['authenticated']);
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

    public static function login($password)
    {
        $hash = Env::get('AUTH_PASSWORD', '');
        if ($hash && password_verify($password, $hash)) {
            session_regenerate_id(true);
            $_SESSION['authenticated'] = true;
            $_SESSION['auth_time'] = time();
            return true;
        }
        return false;
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

<?php
// CSRF Protection Middleware

require_once BASE_PATH . '/app/helpers/Session.php';

class Csrf
{
    public static function generate()
    {
        return Session::getCsrfToken();
    }

    public static function field()
    {
        $token = self::generate();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate()
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            http_response_code(403);
            if (self::isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            } else {
                echo 'Error: Token de seguridad inválido. Recargue la página.';
            }
            exit;
        }
    }

    private static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

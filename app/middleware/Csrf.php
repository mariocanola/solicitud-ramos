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
                // Include fresh token so the client can retry
                header('Content-Type: application/json');
                echo json_encode([
                    'success'    => false,
                    'message'    => 'Token CSRF inválido. Recargue la página.',
                    'csrf_token' => Session::getCsrfToken(),
                ]);
            } else {
                echo 'Error: Token de seguridad inválido. Recargue la página.';
            }
            exit;
        }
        // Don't regenerate on every request — the token is already time-limited.
        // Rotating on each POST causes failures when the user fires two quick AJAX requests.
    }

    private static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

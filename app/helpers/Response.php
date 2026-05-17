<?php
// Standardized HTTP Response Helper

class Response
{
    public static function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        // Anti-cache: las respuestas JSON nunca deben cachearse por browsers o proxies,
        // sobre todo el heartbeat del dashboard y los endpoints de validacion en tiempo real.
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        // Include fresh CSRF token in every JSON response
        if (session_status() === PHP_SESSION_ACTIVE) {
            $data['csrf_token'] = Session::getCsrfToken();
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null, $message = '')
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public static function error($message, $code = 400, $errors = [])
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    public static function redirect($url)
    {
        header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
        exit;
    }
}

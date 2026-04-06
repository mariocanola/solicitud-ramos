<?php
// Session Management Helper

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function delete($key)
    {
        unset($_SESSION[$key]);
    }

    public static function flash($key, $value)
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash($key)
    {
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function generateCsrfToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
        $_SESSION['_csrf_token_time'] = time();
        return $token;
    }

    public static function validateCsrfToken($token)
    {
        return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function getCsrfToken()
    {
        // Regenerate token if it doesn't exist or is older than 30 minutes
        if (!isset($_SESSION['_csrf_token']) || self::csrfTokenExpired()) {
            self::generateCsrfToken();
        }
        return $_SESSION['_csrf_token'];
    }

    private static function csrfTokenExpired()
    {
        $tokenTime = $_SESSION['_csrf_token_time'] ?? 0;
        return (time() - $tokenTime) > 1800; // 30 minutes
    }
}

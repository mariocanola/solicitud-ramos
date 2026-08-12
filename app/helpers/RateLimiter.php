<?php
// File-based rate limiter for login (IP + username).

class RateLimiter
{
    const MAX_ATTEMPTS = 5;
    const WINDOW_SECONDS = 900; // 15 minutes

    public static function tooManyAttempts($username)
    {
        $data = self::read($username);
        if ($data === null) {
            return false;
        }
        if (time() - $data['started'] > self::WINDOW_SECONDS) {
            self::clear($username);
            return false;
        }
        return $data['attempts'] >= self::MAX_ATTEMPTS;
    }

    public static function hit($username)
    {
        $data = self::read($username);
        $now = time();
        if ($data === null || ($now - $data['started']) > self::WINDOW_SECONDS) {
            $data = ['started' => $now, 'attempts' => 0];
        }
        $data['attempts']++;
        self::write($username, $data);
        return $data['attempts'];
    }

    public static function clear($username)
    {
        $file = self::path($username);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    public static function retryAfterMinutes($username)
    {
        $data = self::read($username);
        if ($data === null) {
            return 0;
        }
        $remaining = self::WINDOW_SECONDS - (time() - $data['started']);
        return max(1, (int)ceil($remaining / 60));
    }

    private static function read($username)
    {
        $file = self::path($username);
        if (!is_file($file)) {
            return null;
        }
        $data = json_decode((string)file_get_contents($file), true);
        if (!is_array($data) || !isset($data['started'], $data['attempts'])) {
            return null;
        }
        return $data;
    }

    private static function write($username, array $data)
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        file_put_contents(self::path($username), json_encode($data), LOCK_EX);
    }

    private static function path($username)
    {
        return self::dir() . '/' . self::key($username) . '.json';
    }

    private static function dir()
    {
        return STORAGE_PATH . '/ratelimit';
    }

    private static function key($username)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return hash('sha256', strtolower(trim((string)$username)) . '|' . $ip);
    }
}

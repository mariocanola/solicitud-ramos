<?php
// Simple .env file parser

class Env
{
    private static $loaded = false;

    public static function load($path)
    {
        if (self::$loaded) return;

        $file = rtrim($path, '/\\') . '/.env';
        if (!file_exists($file)) return;

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;

            $pos = strpos($line, '=');
            if ($pos === false) continue;

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            // Remove surrounding quotes
            if (strlen($value) >= 2) {
                $first = $value[0];
                if (($first === '"' || $first === "'") && $value[strlen($value) - 1] === $first) {
                    $value = substr($value, 1, -1);
                }
            }

            $_ENV[$key] = $value;
            putenv("$key=$value");
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

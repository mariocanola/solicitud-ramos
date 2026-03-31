<?php
// Input Validation Helper

class Validator
{
    public static function required($value)
    {
        return $value !== null && trim((string)$value) !== '';
    }

    public static function minLength($value, $min)
    {
        return mb_strlen(trim((string)$value)) >= $min;
    }

    public static function maxLength($value, $max)
    {
        return mb_strlen(trim((string)$value)) <= $max;
    }

    public static function numeric($value)
    {
        return is_numeric($value);
    }

    public static function integer($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function date($value)
    {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }

    public static function inArray($value, array $allowed)
    {
        return in_array($value, $allowed, true);
    }

    public static function onlyLetters($value)
    {
        return preg_match('/^[\p{L}\s]+$/u', $value);
    }

    public static function onlyNumbers($value)
    {
        return preg_match('/^[0-9]+$/', $value);
    }

    public static function sanitize($value)
    {
        return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeArray(array $data)
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $clean[$key] = is_string($value) ? self::sanitize($value) : $value;
        }
        return $clean;
    }
}

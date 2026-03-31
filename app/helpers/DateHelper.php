<?php
// Date Utility Helper

class DateHelper
{
    public static function today()
    {
        return date('Y-m-d');
    }

    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    public static function currentPeriod()
    {
        return date('Y-m-01');
    }

    public static function periodFromDate($date)
    {
        return date('Y-m-01', strtotime($date));
    }

    public static function format($date, $format = 'd/m/Y')
    {
        $dt = new DateTime($date);
        return $dt->format($format);
    }

    public static function isValidDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public static function isFuture($date)
    {
        return strtotime($date) > strtotime(self::today());
    }

    public static function mesAnio($date)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $dt = new DateTime($date);
        return $meses[(int)$dt->format('m')] . ' ' . $dt->format('Y');
    }
}

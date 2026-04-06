<?php
// Database Configuration

return [
    'host'     => Env::get('DB_HOST', 'localhost'),
    'dbname'   => Env::get('DB_NAME', 'flores_db'),
    'username' => Env::get('DB_USER', 'root'),
    'password' => Env::get('DB_PASS', ''),
    'charset'  => Env::get('DB_CHARSET', 'utf8mb4'),
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];

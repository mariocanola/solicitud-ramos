<?php
/**
 * Script CLI para crear los usuarios iniciales del sistema.
 *
 * Uso:
 *   php sql/install_users.php
 *
 * Crea (si no existen) un usuario admin y un usuario operador con contrasenas
 * por defecto. Despues del primer login, cambialas desde el panel de admin.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este script solo puede ejecutarse desde la linea de comandos.\n");
}

require_once __DIR__ . '/../app/config/app.php';
require_once BASE_PATH . '/app/models/Usuario.php';

$usuarios = [
    [
        'username' => 'admin',
        'password' => 'admin123',
        'nombre'   => 'Administrador',
        'rol'      => 'admin',
    ],
    [
        'username' => 'operador',
        'password' => 'operador123',
        'nombre'   => 'Operador',
        'rol'      => 'operador',
    ],
];

// Conexion directa para hacer upsert (reinstalar siempre con hash fresco)
require_once BASE_PATH . '/app/models/Database.php';
$db = Database::getInstance()->getConnection();

foreach ($usuarios as $u) {
    $hash = password_hash($u['password'], PASSWORD_BCRYPT);

    // Verificacion inmediata: el hash recien creado debe validar contra la contrasena
    if (!password_verify($u['password'], $hash)) {
        echo "[FAIL] No se pudo generar un hash valido para '{$u['username']}'. Abortando.\n";
        exit(1);
    }

    $stmt = $db->prepare(
        "INSERT INTO usuarios (username, password_hash, nombre, rol, activo)
         VALUES (?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
            password_hash = VALUES(password_hash),
            nombre = VALUES(nombre),
            rol = VALUES(rol),
            activo = 1"
    );
    $stmt->execute([$u['username'], $hash, $u['nombre'], $u['rol']]);

    echo "[ok] Usuario '{$u['username']}' listo (rol={$u['rol']}, password='{$u['password']}').\n";

    // Re-leer de la DB y re-verificar el hash para confirmar que lo guardo bien
    $stmt2 = $db->prepare("SELECT password_hash FROM usuarios WHERE username = ?");
    $stmt2->execute([$u['username']]);
    $row = $stmt2->fetch();
    if ($row && password_verify($u['password'], $row['password_hash'])) {
        echo "       -> verificado OK contra la base de datos.\n";
    } else {
        echo "       -> [FAIL] El hash leido de la base NO valida. Revisa la columna password_hash (puede ser muy corta).\n";
    }
}

echo "\nListo. IMPORTANTE: cambia las contrasenas por defecto en produccion.\n";

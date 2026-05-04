<?php
/**
 * Script de diagnostico para probar el login desde la linea de comandos.
 *
 * Uso:
 *   php sql/test_login.php admin admin123
 *   php sql/test_login.php operador operador123
 */

if (PHP_SAPI !== 'cli') {
    exit("Solo CLI.\n");
}

if ($argc < 3) {
    echo "Uso: php sql/test_login.php <username> <password>\n";
    exit(1);
}

$username = $argv[1];
$password = $argv[2];

require_once __DIR__ . '/../app/config/app.php';
require_once BASE_PATH . '/app/models/Database.php';

echo "=== Diagnostico de login ===\n";
echo "Username: '{$username}'\n";
echo "Password: '{$password}' (longitud: " . strlen($password) . ")\n\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "[ok] Conexion a base de datos exitosa.\n";
} catch (Throwable $e) {
    echo "[FAIL] Error de conexion: " . $e->getMessage() . "\n";
    exit(1);
}

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "[FAIL] No existe ningun usuario con username = '{$username}'.\n";
    echo "       Verifica con: SELECT username FROM usuarios;\n";
    exit(1);
}

echo "[ok] Usuario encontrado:\n";
echo "     id       = {$user['id']}\n";
echo "     username = '{$user['username']}'\n";
echo "     nombre   = '{$user['nombre']}'\n";
echo "     rol      = '{$user['rol']}'\n";
echo "     activo   = {$user['activo']}\n";
echo "     hash     = '" . substr($user['password_hash'], 0, 30) . "...' (longitud total: " . strlen($user['password_hash']) . ")\n\n";

if ((int)$user['activo'] !== 1) {
    echo "[FAIL] El usuario existe pero esta INACTIVO. Login bloqueado.\n";
    exit(1);
}

if (strlen($user['password_hash']) < 60) {
    echo "[FAIL] El hash es demasiado corto (deberia tener 60 chars). La columna password_hash puede estar truncada.\n";
    echo "       Ejecuta: ALTER TABLE usuarios MODIFY password_hash VARCHAR(255) NOT NULL;\n";
    exit(1);
}

if (password_verify($password, $user['password_hash'])) {
    echo "[OK] password_verify() exitoso. Las credenciales son CORRECTAS.\n";
    echo "     El login deberia funcionar desde el navegador.\n";
} else {
    echo "[FAIL] password_verify() FALLO. La contrasena no coincide con el hash.\n";
    echo "       Posibles causas:\n";
    echo "       1. Estas escribiendo otra contrasena\n";
    echo "       2. El hash en la DB esta corrupto\n";
    echo "       Solucion: corre 'php sql/install_users.php' para regenerar los hashes.\n";
}

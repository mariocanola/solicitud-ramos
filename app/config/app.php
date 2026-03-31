<?php
// Application Configuration

define('BASE_PATH', dirname(__DIR__, 2));

// Auto-detect BASE_URL from the request so it works under both
// the PHP built-in server (root = public/) and Apache/Laragon
// where the app lives at /flores/public/.
$_scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $_scriptDir);
define('APP_NAME', 'Sistema de Ramos Florales');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('PDF_PATH', STORAGE_PATH . '/pdfs');
define('LOG_PATH', STORAGE_PATH . '/logs');
define('ITEMS_PER_PAGE', 20);
define('CUPO_DEFAULT', 50);

// Timezone
date_default_timezone_set('America/Bogota');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/error.log');

// Ensure storage directories exist
if (!is_dir(PDF_PATH)) {
    @mkdir(PDF_PATH, 0755, true);
}
if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0755, true);
}

<?php

/**
 * JUSEA CMN v2.0 - Punto de Entrada (Front Controller)
 * CodeIgniter 4
 */

// Version minima de PHP
$minPhpVersion = '8.1';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    header('HTTP/1.1 503 Service Unavailable', true, 503);
    echo "PHP $minPhpVersion+ requerido. Actual: " . PHP_VERSION;
    exit(1);
}

// =====================================================
// DETECTAR entorno desde .env (antes de que arranque CI)
// Local  → CI_ENVIRONMENT=development en .env
// Server → CI_ENVIRONMENT=production  en .env
// =====================================================
$_ciEnv = 'production'; // default seguro
$_envFile = __DIR__ . '/../.env';
if (file_exists($_envFile)) {
    $_envContent = file_get_contents($_envFile);
    if (preg_match('/^CI_ENVIRONMENT\s*=\s*(\w+)/m', $_envContent, $_m)) {
        $_ciEnv = trim($_m[1]);
    }
}
$_SERVER['CI_ENVIRONMENT'] = $_ciEnv;
$_ENV['CI_ENVIRONMENT']    = $_ciEnv;
putenv('CI_ENVIRONMENT=' . $_ciEnv);
define('ENVIRONMENT', $_ciEnv);
unset($_ciEnv, $_envFile, $_envContent, $_m);

// Ruta al Front Controller
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);

// Reparar .env si tiene espacios (fix automatico una sola vez)
$envFile = FCPATH . '../.env';
if (file_exists($envFile)) {
    $content = file_get_contents($envFile);
    if (strpos($content, 'CI_ENVIRONMENT = ') !== false) {
        $content = str_replace('CI_ENVIRONMENT = ', 'CI_ENVIRONMENT=', $content);
        $content = str_replace('database.default.hostname = ', 'database.default.hostname=', $content);
        $content = str_replace('database.default.database = ', 'database.default.database=', $content);
        $content = str_replace('database.default.username = ', 'database.default.username=', $content);
        $content = str_replace('database.default.password = ', 'database.default.password=', $content);
        $content = str_replace('database.default.password =', 'database.default.password=', $content);
        $content = str_replace('database.default.DBDriver = ', 'database.default.DBDriver=', $content);
        $content = str_replace('database.default.port = ', 'database.default.port=', $content);
        $content = str_replace("app.baseURL = ", "app.baseURL=", $content);
        @file_put_contents($envFile, $content);
    }
}

// Cargar rutas de la aplicacion
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();

// Cargar CodeIgniter
require $paths->systemDirectory . '/Boot.php';

exit(\CodeIgniter\Boot::bootWeb($paths));

<?php
/**
 * JUSEA CMN v2.0 - Script de reparacion completa v3
 * ESTRATEGIA: Copiar configs DEFAULT de CI4 del vendor, luego personalizar
 * Ejecutar con: C:\xampp\php\php.exe reparar.php
 */

echo "JUSEA CMN v2.0 - Reparacion completa v3\n";
echo "========================================\n\n";

$htdocs = 'C:/xampp/htdocs/JuseaCMN_v2';
$source = dirname(__FILE__);
$vendor_app = $htdocs . '/vendor/codeigniter4/framework/app';

$ok = 0;
$err = 0;

// ==========================================
// 1. Copiar TODOS los Config del vendor de CI4
// ==========================================
echo "[1/5] Copiando configs por defecto de CI4...\n";

if (!is_dir($vendor_app . '/Config')) {
    echo "  [ERROR FATAL] No se encontro $vendor_app/Config\n";
    echo "  Verifique que composer install se ejecuto correctamente.\n";
    exit(1);
}

// Copiar TODA la carpeta Config del vendor al proyecto
function copyDirRecursive($src, $dst) {
    $count = 0;
    if (!is_dir($src)) return 0;
    if (!is_dir($dst)) mkdir($dst, 0755, true);

    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            $count += copyDirRecursive($srcPath, $dstPath);
        } else {
            // Solo copiar si NO existe en destino (no sobreescribir archivos ya personalizados)
            // EXCEPTO App.php que necesitamos el default correcto
            if (copy($srcPath, $dstPath)) {
                $count++;
            }
        }
    }
    closedir($dir);
    return $count;
}

// Primero, copiar TODO el Config del vendor (tiene todas las propiedades correctas)
$n = copyDirRecursive($vendor_app . '/Config', $htdocs . '/app/Config');
echo "  [OK] $n archivos de Config copiados desde vendor\n";
$ok += $n;

// Copiar Common.php del vendor si existe
if (file_exists($vendor_app . '/Common.php')) {
    copy($vendor_app . '/Common.php', $htdocs . '/app/Common.php');
    echo "  [OK] Common.php copiado desde vendor\n";
    $ok++;
}

// ==========================================
// 2. PERSONALIZAR los configs para JUSEA
// ==========================================
echo "\n[2/5] Personalizando configuracion para JUSEA CMN...\n";

// --- App.php: cambiar baseURL, timezone, locale ---
$appFile = $htdocs . '/app/Config/App.php';
if (file_exists($appFile)) {
    $content = file_get_contents($appFile);

    // Cambiar baseURL
    $content = preg_replace(
        "/public string \\\$baseURL = '[^']*';/",
        "public string \$baseURL = 'http://localhost/JuseaCMN_v2/public/';",
        $content
    );

    // Cambiar timezone
    $content = preg_replace(
        "/public string \\\$appTimezone = '[^']*';/",
        "public string \$appTimezone = 'America/Argentina/Buenos_Aires';",
        $content
    );

    // Cambiar locale
    $content = preg_replace(
        "/public string \\\$defaultLocale = '[^']*';/",
        "public string \$defaultLocale = 'es';",
        $content
    );

    // Cambiar supportedLocales
    $content = preg_replace(
        "/public array \\\$supportedLocales = \[[^\]]*\];/",
        "public array \$supportedLocales = ['es'];",
        $content
    );

    // Forzar indexPage a 'index.php' para XAMPP (sin mod_rewrite limpio)
    $content = preg_replace(
        "/public string \\\$indexPage = '[^']*';/",
        "public string \$indexPage = 'index.php';",
        $content
    );

    file_put_contents($appFile, $content);
    echo "  [OK] App.php personalizado (baseURL, timezone, locale, indexPage)\n";
}

// --- Paths.php: CORREGIR systemDirectory para apuntar al vendor ---
$pathsFile = $htdocs . '/app/Config/Paths.php';
if (file_exists($pathsFile)) {
    $content = file_get_contents($pathsFile);
    // El vendor default tiene: __DIR__ . '/../../system'
    // Nosotros necesitamos: __DIR__ . '/../../vendor/codeigniter4/framework/system'
    $content = str_replace(
        "'/../../system'",
        "'/../../vendor/codeigniter4/framework/system'",
        $content
    );
    // Tambien cubrir la variante con comillas dobles
    $content = str_replace(
        '"/../../system"',
        '"/../../vendor/codeigniter4/framework/system"',
        $content
    );
    // Cubrir variante con __DIR__ . concatenacion
    $content = preg_replace(
        "/\\\$systemDirectory\s*=\s*__DIR__\s*\.\s*['\"]\/\.\.\/\.\.\/system['\"]/",
        "\$systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system'",
        $content
    );
    file_put_contents($pathsFile, $content);
    echo "  [OK] Paths.php corregido (systemDirectory -> vendor)\n";
}

// --- Database.php: usar nuestro config ---
$dbContent = <<<'PHP'
<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $defaultGroup = 'default';
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => 'root',
        'password'     => '',
        'database'     => 'jusea_cmn',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberConnect' => false,
    ];

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
PHP;
file_put_contents($htdocs . '/app/Config/Database.php', $dbContent);
echo "  [OK] Database.php personalizado (jusea_cmn, root)\n";

// --- Routes.php: usar nuestras rutas ---
$routesFile = $source . '/app/Config/Routes.php';
if (file_exists($routesFile)) {
    copy($routesFile, $htdocs . '/app/Config/Routes.php');
    echo "  [OK] Routes.php copiado desde proyecto fuente\n";
}

// --- Filters.php: usar nuestros filtros ---
$filtersFile = $source . '/app/Config/Filters.php';
if (file_exists($filtersFile)) {
    copy($filtersFile, $htdocs . '/app/Config/Filters.php');
    echo "  [OK] Filters.php copiado desde proyecto fuente\n";
}

// ==========================================
// 3. Copiar index.php, .env, .htaccess
// ==========================================
echo "\n[3/5] Copiando archivos del sistema...\n";

// .env
$envContent = "#--------------------------------------------------------------------\n";
$envContent .= "# JUSEA CMN v2.0\n";
$envContent .= "#--------------------------------------------------------------------\n\n";
$envContent .= "CI_ENVIRONMENT=development\n\n";
$envContent .= "database.default.hostname=localhost\n";
$envContent .= "database.default.database=jusea_cmn\n";
$envContent .= "database.default.username=root\n";
$envContent .= "database.default.password=\n";
$envContent .= "database.default.DBDriver=MySQLi\n";
$envContent .= "database.default.port=3306\n\n";
$envContent .= "app.baseURL='http://localhost/JuseaCMN_v2/public/'\n";
file_put_contents($htdocs . '/.env', $envContent);
echo "  [OK] .env\n";

// index.php (con fix de entorno)
$indexB64 = 'PD9waHAKCi8qKgogKiBKVVNFQSBDTU4gdjIuMCAtIFB1bnRvIGRlIEVudHJhZGEgKEZyb250IENvbnRyb2xsZXIpCiAqIENvZGVJZ25pdGVyIDQKICovCgovLyBWZXJzaW9uIG1pbmltYSBkZSBQSFAKJG1pblBocFZlcnNpb24gPSAnOC4xJzsKaWYgKHZlcnNpb25fY29tcGFyZShQSFBfVkVSU0lPTiwgJG1pblBocFZlcnNpb24sICc8JykpIHsKICAgIGhlYWRlcignSFRUUC8xLjEgNTAzIFNlcnZpY2UgVW5hdmFpbGFibGUnLCB0cnVlLCA1MDMpOwogICAgZWNobyAiUEhQICRtaW5QaHBWZXJzaW9uKyByZXF1ZXJpZG8uIEFjdHVhbDogIiAuIFBIUF9WRVJTSU9OOwogICAgZXhpdCgxKTsKfQoKLy8gPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0KLy8gRk9SWkFSIGVudG9ybm8gcG9yIFRPREFTIGxhcyB2aWFzIHBvc2libGVzCi8vIEVzdG8gREVCRSBlamVjdXRhcnNlIEFOVEVTIGRlIGNhcmdhciBCb290LnBocAovLyA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQokX1NFUlZFUlsnQ0lfRU5WSVJPTk1FTlQnXSA9ICdkZXZlbG9wbWVudCc7CiRfRU5WWydDSV9FTlZJUk9OTUVOVCddID0gJ2RldmVsb3BtZW50JzsKcHV0ZW52KCdDSV9FTlZJUk9OTUVOVD1kZXZlbG9wbWVudCcpOwpkZWZpbmUoJ0VOVklST05NRU5UJywgJ2RldmVsb3BtZW50Jyk7CgovLyBSdXRhIGFsIEZyb250IENvbnRyb2xsZXIKZGVmaW5lKCdGQ1BBVEgnLCBfX0RJUl9fIC4gRElSRUNUT1JZX1NFUEFSQVRPUik7CmNoZGlyKEZDUEFUSCk7CgovLyBDYXJnYXIgcnV0YXMgZGUgbGEgYXBsaWNhY2lvbgpyZXF1aXJlIEZDUEFUSCAuICcuLi9hcHAvQ29uZmlnL1BhdGhzLnBocCc7CiRwYXRocyA9IG5ldyBDb25maWdcUGF0aHMoKTsKCi8vIENhcmdhciBDb2RlSWduaXRlcgpyZXF1aXJlICRwYXRocy0+c3lzdGVtRGlyZWN0b3J5IC4gJy9Cb290LnBocCc7CgpleGl0KFxDb2RlSWduaXRlclxCb290Ojpib290V2ViKCRwYXRocykpOwo=';
file_put_contents($htdocs . '/public/index.php', base64_decode($indexB64));
echo "  [OK] index.php\n";

// .htaccess
if (file_exists($source . '/public/.htaccess')) {
    copy($source . '/public/.htaccess', $htdocs . '/public/.htaccess');
    echo "  [OK] .htaccess\n";
}

// ==========================================
// 4. Copiar controladores, modelos, vistas, filtros
// ==========================================
echo "\n[4/5] Copiando controladores, modelos y vistas...\n";

$dirs = ['Controllers', 'Models', 'Views', 'Filters', 'Libraries', 'Helpers', 'Database'];
foreach ($dirs as $d) {
    $srcDir = $source . '/app/' . $d;
    $dstDir = $htdocs . '/app/' . $d;
    if (is_dir($srcDir)) {
        $n = copyDirRecursive($srcDir, $dstDir);
        echo "  [OK] app/$d/ ($n archivos)\n";
        $ok += $n;
    }
}

// Crear directorios writable
$writeDirs = ['cache', 'logs', 'session', 'debugbar', 'uploads'];
foreach ($writeDirs as $wd) {
    $path = $htdocs . '/writable/' . $wd;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}
echo "  [OK] Directorios writable creados\n";

// ==========================================
// 5. Verificacion final
// ==========================================
echo "\n[5/5] Verificacion final...\n";

$checks = [
    '.env' => $htdocs . '/.env',
    'index.php' => $htdocs . '/public/index.php',
    'App.php' => $htdocs . '/app/Config/App.php',
    'Database.php' => $htdocs . '/app/Config/Database.php',
    'Routes.php' => $htdocs . '/app/Config/Routes.php',
    'Modules.php' => $htdocs . '/app/Config/Modules.php',
    'Services.php' => $htdocs . '/app/Config/Services.php',
    'Boot/dev' => $htdocs . '/app/Config/Boot/development.php',
    'vendor' => $htdocs . '/vendor/autoload.php',
];
foreach ($checks as $name => $path) {
    echo "  $name: " . (file_exists($path) ? "OK" : "FALTA") . "\n";
}

// Verificar que Paths.php apunta al vendor
$pathsContent = file_get_contents($htdocs . '/app/Config/Paths.php');
echo "  Paths.php->vendor: " . (strpos($pathsContent, 'vendor/codeigniter4/framework/system') !== false ? "OK" : "FALTA") . "\n";

// Verificar que App.php tiene allowedHostnames
$appContent = file_get_contents($htdocs . '/app/Config/App.php');
echo "  App.php->allowedHostnames: " . (strpos($appContent, 'allowedHostnames') !== false ? "OK" : "FALTA") . "\n";
echo "  App.php->uriProtocol: " . (strpos($appContent, 'uriProtocol') !== false ? "OK" : "FALTA") . "\n";
echo "  App.php->baseURL(JUSEA): " . (strpos($appContent, 'JuseaCMN_v2') !== false ? "OK" : "FALTA") . "\n";

echo "\n========================================\n";
echo "Reparacion completada. $ok archivos procesados.\n";
echo "Abra: http://localhost/JuseaCMN_v2/public/\n";

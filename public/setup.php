<?php
/**
 * JUSEA CMN v2.0 - Script de verificacion y auto-reparacion
 * Ejecutar UNA vez desde el navegador: localhost/JuseaCMN_v2/public/setup.php
 * ELIMINAR despues de la instalacion.
 */

echo "<h2>JUSEA CMN v2.0 - Verificacion de instalacion</h2>";
echo "<pre>";

// 1. Verificar PHP
echo "[OK] PHP version: " . PHP_VERSION . "\n";

// 2. Verificar extensiones
$exts = ['mysqli', 'mbstring', 'curl', 'gd', 'zip', 'xml'];
foreach ($exts as $ext) {
    $status = extension_loaded($ext) ? 'OK' : 'FALTA';
    echo "[$status] Extension: $ext\n";
}

// 3. Verificar Composer/vendor
$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorPath)) {
    echo "[OK] Composer vendor/autoload.php encontrado\n";
} else {
    echo "[ERROR] NO se encontro vendor/autoload.php\n";
    echo "  -> Ejecute: cd " . realpath(__DIR__ . '/..') . " && composer install\n";
}

// 4. Verificar .env
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    echo "[OK] Archivo .env encontrado\n";
} else {
    // Intentar crear desde .env.example
    $examplePath = __DIR__ . '/../.env.example';
    if (file_exists($examplePath)) {
        copy($examplePath, $envPath);
        echo "[OK] Archivo .env creado desde .env.example\n";
    } else {
        echo "[ERROR] No se encontro .env ni .env.example\n";
    }
}

// 5. Verificar writable
$writableDirs = ['cache', 'documents', 'logs', 'session', 'uploads'];
$writablePath = __DIR__ . '/../writable';
foreach ($writableDirs as $dir) {
    $full = "$writablePath/$dir";
    if (!is_dir($full)) {
        @mkdir($full, 0777, true);
        echo "[CREADO] writable/$dir\n";
    } else {
        $writable = is_writable($full) ? 'OK' : 'SIN PERMISOS';
        echo "[$writable] writable/$dir\n";
    }
}

// 6. Verificar index.php
if (file_exists(__DIR__ . '/index.php')) {
    echo "[OK] public/index.php existe\n";
} else {
    echo "[ERROR] public/index.php NO existe - creandolo...\n";
    $indexContent = '<?php
$minPhpVersion = \'8.1\';
if (version_compare(PHP_VERSION, $minPhpVersion, \'<\')) {
    header(\'HTTP/1.1 503 Service Unavailable\', true, 503);
    echo sprintf(\'PHP %s+ requerido. Actual: %s\', $minPhpVersion, PHP_VERSION);
    exit(1);
}
define(\'FCPATH\', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . \'../app/Config/Paths.php\';
$paths = new Config\Paths();
require $paths->systemDirectory . \'/Boot.php\';
exit(\CodeIgniter\Boot::bootWeb($paths));
';
    file_put_contents(__DIR__ . '/index.php', $indexContent);
    echo "[OK] public/index.php creado automaticamente\n";
}

// 7. Verificar .htaccess
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "[OK] public/.htaccess existe\n";
} else {
    echo "[ERROR] public/.htaccess NO existe - creandolo...\n";
    $htContent = '<IfModule mod_rewrite.c>
    Options -Multiviews
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
';
    file_put_contents(__DIR__ . '/.htaccess', $htContent);
    echo "[OK] public/.htaccess creado automaticamente\n";
}

// 8. Verificar conexion a BD
echo "\n--- Prueba de conexion a BD ---\n";
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'jusea_cmn';

// Leer .env si existe
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, 'database.default.hostname') !== false) {
            $host = trim(explode('=', $line, 2)[1] ?? $host);
        }
        if (strpos($line, 'database.default.username') !== false) {
            $user = trim(explode('=', $line, 2)[1] ?? $user);
        }
        if (strpos($line, 'database.default.password') !== false) {
            $pass = trim(explode('=', $line, 2)[1] ?? $pass);
        }
        if (strpos($line, 'database.default.database') !== false) {
            $db = trim(explode('=', $line, 2)[1] ?? $db);
        }
    }
}

$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo "[ERROR] No se pudo conectar: " . $conn->connect_error . "\n";
} else {
    echo "[OK] Conexion a BD '$db' exitosa\n";
    $result = $conn->query("SHOW TABLES");
    echo "[OK] Tablas encontradas: " . $result->num_rows . "\n";

    // Verificar usuario admin
    $result = $conn->query("SELECT username, rol, activo FROM usuarios WHERE username = 'admin'");
    if ($row = $result->fetch_assoc()) {
        echo "[OK] Usuario admin existe (rol: {$row['rol']}, activo: {$row['activo']})\n";
    } else {
        echo "[AVISO] No existe usuario admin\n";
    }
    $conn->close();
}

echo "\n--- Verificacion completada ---\n";
echo "</pre>";

// Verificar Paths.php
$pathsFile = __DIR__ . '/../app/Config/Paths.php';
if (file_exists($pathsFile)) {
    echo "<p style='color:green'><b>Todo listo!</b> Acceda al sistema: <a href='index.php'>Ir a JUSEA CMN v2.0</a></p>";
} else {
    echo "<p style='color:red'><b>FALTA app/Config/Paths.php</b> - Copie la carpeta actualizada del proyecto.</p>";
}

echo "<p style='color:red'><b>IMPORTANTE:</b> Elimine este archivo (setup.php) despues de verificar.</p>";

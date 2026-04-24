<?php
/**
 * JUSEA CMN v2.0 - Script de reparacion rapida
 * Abre esto en el navegador: localhost/JuseaCMN_v2/public/fix.php
 * ELIMINAR despues de usar.
 */
echo "<h2>JUSEA CMN v2.0 - Reparacion rapida</h2><pre>";

$root = realpath(__DIR__ . '/..');

// 1. Crear .env correcto
$envContent = '#--------------------------------------------------------------------
# JUSEA CMN v2.0
#--------------------------------------------------------------------

CI_ENVIRONMENT=development

database.default.hostname=localhost
database.default.database=jusea_cmn
database.default.username=root
database.default.password=
database.default.DBDriver=MySQLi
database.default.port=3306

app.baseURL=\'http://localhost/JuseaCMN_v2/public/\'
';

file_put_contents($root . '/.env', $envContent);
echo "[OK] Archivo .env creado correctamente (sin espacios)\n";

// 2. Verificar vendor
if (file_exists($root . '/vendor/autoload.php')) {
    echo "[OK] vendor/autoload.php existe\n";
} else {
    echo "[ERROR] vendor/autoload.php NO existe - ejecute REPARAR_JUSEA.bat\n";
}

// 3. Verificar Boot.php
$systemDir = $root . '/vendor/codeigniter4/framework/system';
if (file_exists($systemDir . '/Boot.php')) {
    echo "[OK] CodeIgniter Boot.php encontrado\n";
} else {
    echo "[ERROR] Boot.php no encontrado en: $systemDir\n";
    // Buscar donde esta
    $alt = $root . '/vendor/codeigniter4';
    if (is_dir($alt)) {
        echo "  Contenido de vendor/codeigniter4/:\n";
        foreach (scandir($alt) as $f) {
            if ($f !== '.' && $f !== '..') echo "    $f\n";
        }
    }
}

// 4. Verificar Paths.php
$pathsFile = $root . '/app/Config/Paths.php';
if (file_exists($pathsFile)) {
    echo "[OK] Paths.php existe\n";
    // Leer y verificar systemDirectory
    $content = file_get_contents($pathsFile);
    if (strpos($content, 'vendor/codeigniter4/framework/system') !== false) {
        echo "[OK] Paths.php apunta a vendor/codeigniter4/framework/system\n";
    } else {
        echo "[AVISO] Paths.php podria tener ruta incorrecta\n";
    }
} else {
    echo "[ERROR] Paths.php NO existe\n";
}

// 5. Verificar extensiones
$exts = ['mysqli', 'intl', 'mbstring', 'gd', 'zip'];
foreach ($exts as $ext) {
    $s = extension_loaded($ext) ? 'OK' : 'FALTA';
    echo "[$s] Extension: $ext\n";
}

// 6. Test de conexion BD
$conn = @new mysqli('localhost', 'root', '', 'jusea_cmn');
if ($conn->connect_error) {
    echo "[ERROR] BD: " . $conn->connect_error . "\n";
} else {
    echo "[OK] Conexion a BD exitosa\n";
    $conn->close();
}

echo "\n--- Reparacion completada ---\n";
echo "</pre>";
echo "<p><a href='index.php'><b>Ir a JUSEA CMN v2.0</b></a></p>";
echo "<p style='color:red'><b>Elimine este archivo (fix.php) despues de verificar.</b></p>";

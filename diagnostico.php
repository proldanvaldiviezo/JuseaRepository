<?php
/**
 * Diagnostico profundo de CI4
 * Ejecutar con: C:\xampp\php\php.exe diagnostico.php
 * Crea un archivo diagnostico en htdocs que se puede ver en el navegador
 */

$htdocs = 'C:/xampp/htdocs/JuseaCMN_v2';
$output = '';

$output .= "<?php\nheader('Content-Type: text/plain; charset=utf-8');\n";

// Read Boot.php and embed it as a string
$bootPath = $htdocs . '/vendor/codeigniter4/framework/system/Boot.php';
if (file_exists($bootPath)) {
    $bootContent = file_get_contents($bootPath);
    $bootB64 = base64_encode($bootContent);

    $output .= "echo '=== DIAGNOSTICO PROFUNDO JUSEA CMN v2.0 ===' . PHP_EOL . PHP_EOL;\n\n";

    // Show Boot.php info
    $output .= "echo '1. BOOT.PHP (' . " . strlen($bootContent) . " . ' bytes):' . PHP_EOL;\n";
    $output .= "\$boot = base64_decode('$bootB64');\n";
    $output .= "echo \$boot;\n";
    $output .= "echo PHP_EOL . PHP_EOL;\n";

    // Show index.php
    $output .= "echo '2. INDEX.PHP:' . PHP_EOL;\n";
    $output .= "echo file_get_contents(__DIR__ . '/index.php');\n";
    $output .= "echo PHP_EOL . PHP_EOL;\n";

    // Show .env
    $output .= "echo '3. .ENV:' . PHP_EOL;\n";
    $output .= "echo file_get_contents(__DIR__ . '/../.env');\n";
    $output .= "echo PHP_EOL . PHP_EOL;\n";

    // Show Constants.php if exists
    $output .= "echo '4. APP/CONFIG/CONSTANTS.PHP:' . PHP_EOL;\n";
    $output .= "\$cf = __DIR__ . '/../app/Config/Constants.php';\n";
    $output .= "if(file_exists(\$cf)) { echo file_get_contents(\$cf); } else { echo 'NO EXISTE'; }\n";
    $output .= "echo PHP_EOL . PHP_EOL;\n";

    // List Boot directory
    $output .= "echo '5. APP/CONFIG/BOOT/ DIRECTORY:' . PHP_EOL;\n";
    $output .= "\$bd = __DIR__ . '/../app/Config/Boot/';\n";
    $output .= "if(is_dir(\$bd)) { foreach(scandir(\$bd) as \$f) { if(\$f !== '.' && \$f !== '..') echo '  ' . \$f . PHP_EOL; } } else { echo '  DIRECTORIO NO EXISTE'; }\n";
    $output .= "echo PHP_EOL;\n";

    file_put_contents($htdocs . '/public/ver_boot.php', $output);
    echo "Diagnostico creado en: $htdocs/public/ver_boot.php\n";
    echo "Abrir: http://localhost/JuseaCMN_v2/public/ver_boot.php\n";
} else {
    echo "ERROR: No se encontro Boot.php en: $bootPath\n";

    // Try to find it
    echo "Buscando Boot.php...\n";
    $vendorDir = $htdocs . '/vendor';
    if (is_dir($vendorDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($vendorDir)
        );
        foreach ($iterator as $file) {
            if ($file->getFilename() === 'Boot.php') {
                echo "  Encontrado: " . $file->getPathname() . "\n";
            }
        }
    } else {
        echo "  /vendor no existe!\n";
    }
}

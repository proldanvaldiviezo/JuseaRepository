<?php
/**
 * JUSEA — Diagnóstico de conectividad APICPS
 * ELIMINAR del servidor una vez usado.
 */
// Sin restricción de IP — script temporal, eliminarlo después de usar.
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<title>Diagnóstico APICPS</title>
<style>
body{font-family:system-ui,sans-serif;padding:24px;background:#f5f7fa}
.card{background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.1);padding:20px;margin-bottom:16px}
.ok{color:#16a34a} .err{color:#dc2626} .warn{color:#ca8a04}
pre{background:#f3f4f6;padding:12px;border-radius:6px;font-size:.82rem;overflow-x:auto;white-space:pre-wrap}
h2{font-size:1rem;margin:0 0 12px}
</style></head><body>
<h1 style="font-size:1.3rem">🔍 Diagnóstico APICPS — <?= gethostname() ?></h1>

<?php
// ── 1. Extensión curl ────────────────────────────────────────────────────
echo '<div class="card"><h2>1. Extensión curl</h2>';
if (extension_loaded('curl')) {
    $v = curl_version();
    echo '<p class="ok">✅ curl habilitado — versión ' . $v['version'] . ' / SSL: ' . $v['ssl_version'] . '</p>';
} else {
    echo '<p class="err">❌ curl NO está habilitado en PHP. Habilitá extension=curl en php.ini.</p>';
}
echo '</div>';

// ── 2. Variables de entorno relevantes ───────────────────────────────────
echo '<div class="card"><h2>2. Variables .env relevantes</h2>';
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $relevantes = array_filter($lines, fn($l) => str_starts_with($l, 'APICPS') || str_starts_with($l, 'CI_ENV') || str_starts_with($l, 'app.base'));
    echo '<pre>' . htmlspecialchars(implode("\n", $relevantes)) . '</pre>';
} else {
    echo '<p class="err">❌ Archivo .env no encontrado en ' . htmlspecialchars(realpath(__DIR__ . '/..')) . '</p>';
}
echo '</div>';

// ── 3. DNS resolution ────────────────────────────────────────────────────
echo '<div class="card"><h2>3. Resolución DNS de apicps.ejercito.mil.ar</h2>';
$host = 'apicps.ejercito.mil.ar';
$ip   = gethostbyname($host);
if ($ip !== $host) {
    echo '<p class="ok">✅ Resuelve a: <strong>' . htmlspecialchars($ip) . '</strong></p>';
} else {
    echo '<p class="err">❌ No se pudo resolver el DNS de <code>' . $host . '</code>. El servidor no tiene acceso o no resuelve ese dominio.</p>';
}
echo '</div>';

// ── 4. Test curl con SSL verify OFF ─────────────────────────────────────
echo '<div class="card"><h2>4. Test curl → APICPS (SSL verify OFF)</h2>';
if (extension_loaded('curl')) {
    $ch = curl_init('https://apicps.ejercito.mil.ar/api/v1/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['username' => '__test__', 'password' => '__test__']),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    $resp    = curl_exec($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerror  = curl_error($ch);
    $cinfo   = curl_getinfo($ch);
    curl_close($ch);

    if ($cerror) {
        echo '<p class="err">❌ Error curl (SSL OFF): <strong>' . htmlspecialchars($cerror) . '</strong></p>';
    } else {
        echo '<p class="ok">✅ Conectó (SSL OFF) — HTTP ' . $code . '</p>';
        echo '<pre>' . htmlspecialchars(substr($resp, 0, 300)) . '</pre>';
    }
    echo '<pre>Total time: ' . round($cinfo['total_time'], 2) . 's · IP conectada: ' . $cinfo['primary_ip'] . '</pre>';
} else {
    echo '<p class="warn">⚠ curl no disponible, omitido.</p>';
}
echo '</div>';

// ── 5. Test curl con SSL verify ON ──────────────────────────────────────
echo '<div class="card"><h2>5. Test curl → APICPS (SSL verify ON)</h2>';
if (extension_loaded('curl')) {
    $ch = curl_init('https://apicps.ejercito.mil.ar/api/v1/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['username' => '__test__', 'password' => '__test__']),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $resp   = curl_exec($ch);
    $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerror = curl_error($ch);
    curl_close($ch);

    if ($cerror) {
        echo '<p class="err">❌ Error curl (SSL ON): <strong>' . htmlspecialchars($cerror) . '</strong></p>';
        echo '<p class="warn">→ Esto confirma que necesitás <code>APICPS_SSL_VERIFY=false</code> en el .env del servidor.</p>';
    } else {
        echo '<p class="ok">✅ Conectó (SSL ON) — HTTP ' . $code . ' (SSL verify funciona correctamente)</p>';
    }
} else {
    echo '<p class="warn">⚠ curl no disponible, omitido.</p>';
}
echo '</div>';

// ── 6. Ruta y permisos de writable/logs ─────────────────────────────────
echo '<div class="card"><h2>6. Directorio de logs</h2>';
$logDir = __DIR__ . '/../writable/logs/';
if (is_dir($logDir)) {
    $escribible = is_writable($logDir);
    echo '<p class="' . ($escribible ? 'ok' : 'err') . '">' . ($escribible ? '✅' : '❌') . ' ' . realpath($logDir) . ' — ' . ($escribible ? 'escribible' : 'SIN permiso de escritura') . '</p>';
    $logs = glob($logDir . 'log-*.log');
    if ($logs) {
        $ultimo = end($logs);
        $lineas = array_slice(file($ultimo), -20);
        echo '<p>Últimas líneas de <code>' . basename($ultimo) . '</code>:</p>';
        echo '<pre>' . htmlspecialchars(implode('', $lineas)) . '</pre>';
    }
} else {
    echo '<p class="err">❌ No existe el directorio writable/logs/</p>';
}
echo '</div>';
?>
<p style="color:#999;font-size:.8rem">⚠ Eliminá <code>diag_apicps.php</code> del servidor una vez revisado el diagnóstico.</p>
</body></html>

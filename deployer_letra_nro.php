<?php
/**
 * JUSEA CMN v2.0 — Migración: campos Letra y Nro
 * Ejecutar UNA SOLA VEZ: http://localhost/JuseaCMN_v2/deployer_letra_nro.php
 * Borrarlo luego de ejecutar.
 */

$host   = 'localhost';
$dbname = 'jusea_cmn';
$user   = 'root';
$pass   = '';

$pasos  = [];
$ok     = true;

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pasos[] = ['paso' => 'Conexión a MySQL',  'ok' => true, 'msg' => 'Conectado a jusea_cmn'];

    // Verificar si las columnas ya existen
    $cols = $pdo->query("SHOW COLUMNS FROM `infracciones` LIKE 'letra'")->fetchAll();
    if (count($cols) > 0) {
        $pasos[] = ['paso' => 'Columna letra', 'ok' => true, 'msg' => 'Ya existe — sin cambios'];
    } else {
        $pdo->exec("ALTER TABLE `infracciones`
            ADD COLUMN `letra` VARCHAR(10)  NULL DEFAULT NULL COMMENT 'Letra identificadora del acto' AFTER `lugar_cumplimiento`");
        $pasos[] = ['paso' => 'Columna letra', 'ok' => true, 'msg' => 'Creada correctamente'];
    }

    $cols2 = $pdo->query("SHOW COLUMNS FROM `infracciones` LIKE 'nro'")->fetchAll();
    if (count($cols2) > 0) {
        $pasos[] = ['paso' => 'Columna nro', 'ok' => true, 'msg' => 'Ya existe — sin cambios'];
    } else {
        $pdo->exec("ALTER TABLE `infracciones`
            ADD COLUMN `nro` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Número correlativo del acto' AFTER `letra`");
        $pasos[] = ['paso' => 'Columna nro', 'ok' => true, 'msg' => 'Creada correctamente'];
    }

} catch (PDOException $e) {
    $ok = false;
    $pasos[] = ['paso' => 'Error PDO', 'ok' => false, 'msg' => $e->getMessage()];
}

// Limpiar cache CI4
$cacheDir = __DIR__ . '/writable/cache/';
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '*') as $f) {
        if (is_file($f)) @unlink($f);
    }
    $pasos[] = ['paso' => 'Cache CI4', 'ok' => true, 'msg' => 'Limpiada'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>JUSEA — Migración Letra/Nro</title>
    <style>
        body  { font-family: monospace; background: #1a1a2e; color: #eee; padding: 40px; }
        h2    { color: #c9a227; }
        table { border-collapse: collapse; margin-top: 20px; }
        td, th { padding: 8px 22px; border: 1px solid #444; text-align: left; }
        .ok   { color: #00d4aa; } .err { color: #ff6b6b; }
        .box  { background: #16213e; padding: 22px; border-radius: 8px; margin-top: 20px; max-width: 640px; }
        .final-ok  { background: #0d3b2e; border:1px solid #00d4aa; padding:16px; border-radius:6px; margin-top:24px; }
        .final-err { background: #3b0d0d; border:1px solid #ff6b6b; padding:16px; border-radius:6px; margin-top:24px; }
    </style>
</head>
<body>
<h2>JUSEA CMN v2.0 — Migración: campos Letra / Nro</h2>
<div class="box">
    <table>
        <tr><th>Paso</th><th>Resultado</th></tr>
        <?php foreach ($pasos as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['paso']) ?></td>
            <td class="<?= $p['ok'] ? 'ok' : 'err' ?>">
                <?= $p['ok'] ? '✔ ' : '✘ ' ?><?= htmlspecialchars($p['msg']) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($ok): ?>
    <div class="final-ok">
        ✅ <strong>Migración completada.</strong><br>
        Los campos <code>Letra</code> y <code>Nro.</code> ya están disponibles en el formulario.<br><br>
        <strong>Borrá este archivo:</strong> <code>deployer_letra_nro.php</code>
    </div>
    <?php else: ?>
    <div class="final-err">
        ⚠️ Se produjo un error. Verificá las credenciales de MySQL o ejecutá el SQL manualmente.
    </div>
    <?php endif; ?>
</div>
</body>
</html>

<?php
/**
 * JUSEA CMN v2.0 - Migración de Base de Datos
 * Ejecuta los ALTER TABLE necesarios para el módulo de sanciones.
 * BORRAR este archivo después de ejecutar la migración.
 */

$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'jusea_cmn';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('<p style="color:red;font-family:monospace">❌ Error de conexión: ' . $conn->connect_error . '</p>');
}

$migraciones = [
    'infracciones.cargo_autoridad'  => "ALTER TABLE infracciones ADD COLUMN IF NOT EXISTS cargo_autoridad VARCHAR(150) DEFAULT '' AFTER lugar_cumplimiento",
    'infracciones.revisor_grado'    => "ALTER TABLE infracciones ADD COLUMN IF NOT EXISTS revisor_grado VARCHAR(80) DEFAULT '' AFTER cargo_autoridad",
    'infracciones.revisor_nombre'   => "ALTER TABLE infracciones ADD COLUMN IF NOT EXISTS revisor_nombre VARCHAR(200) DEFAULT '' AFTER revisor_grado",
    'infracciones.revisor_dni'      => "ALTER TABLE infracciones ADD COLUMN IF NOT EXISTS revisor_dni VARCHAR(10) DEFAULT '' AFTER revisor_nombre",
    'infracciones.revisor_cargo'    => "ALTER TABLE infracciones ADD COLUMN IF NOT EXISTS revisor_cargo VARCHAR(150) DEFAULT '' AFTER revisor_dni",
    'personas.cargo'                => "ALTER TABLE personas ADD COLUMN IF NOT EXISTS cargo VARCHAR(150) DEFAULT '' AFTER destino_interno",
];

$resultados = [];
$hayError   = false;

foreach ($migraciones as $campo => $sql) {
    if ($conn->query($sql) === TRUE) {
        $resultados[$campo] = ['ok' => true,  'msg' => 'OK'];
    } else {
        $resultados[$campo] = ['ok' => false, 'msg' => $conn->error];
        $hayError = true;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>JUSEA - Migración BD</title>
    <style>
        body { font-family: monospace; background: #1a1a2e; color: #eee; padding: 40px; }
        h2   { color: #00d4aa; }
        table { border-collapse: collapse; margin-top: 20px; }
        td, th { padding: 8px 20px; border: 1px solid #444; text-align: left; }
        .ok  { color: #00d4aa; }
        .err { color: #ff6b6b; }
        .box { background: #16213e; padding: 20px; border-radius: 8px; margin-top: 20px; max-width: 600px; }
        .final-ok  { background: #0d3b2e; border: 1px solid #00d4aa; padding: 16px; border-radius: 6px; margin-top: 24px; }
        .final-err { background: #3b0d0d; border: 1px solid #ff6b6b; padding: 16px; border-radius: 6px; margin-top: 24px; }
    </style>
</head>
<body>
    <h2>JUSEA CMN v2.0 — Migración de Base de Datos</h2>
    <div class="box">
        <table>
            <tr><th>Campo</th><th>Resultado</th></tr>
            <?php foreach ($resultados as $campo => $r): ?>
            <tr>
                <td><?= htmlspecialchars($campo) ?></td>
                <td class="<?= $r['ok'] ? 'ok' : 'err' ?>">
                    <?= $r['ok'] ? '✔ ' : '✘ ' ?><?= htmlspecialchars($r['msg']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php if (!$hayError): ?>
        <div class="final-ok">
            ✅ <strong>Migración completada.</strong><br>
            Las columnas fueron agregadas correctamente.<br>
            Ahora podés registrar sanciones sin el error de transacción.<br><br>
            <strong>Borrá este archivo:</strong> <code>public/migrar.php</code>
        </div>
        <?php else: ?>
        <div class="final-err">
            ⚠️ <strong>Hubo errores.</strong> Revisá los mensajes de arriba.<br>
            Verificá que MySQL esté corriendo y que la base de datos <code>jusea_cmn</code> exista.
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

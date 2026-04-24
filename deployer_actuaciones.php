<?php
/**
 * JUSEA CMN v2.0 — Deployer: Tabla actuaciones
 * -----------------------------------------------
 * Crea la tabla `actuaciones` necesaria para los módulos:
 *   - Bienes del Estado (pérdida / ruptura / inutilización)
 *   - Accidente / Enfermedad
 *
 * Ejecutar UNA SOLA VEZ en:
 *   http://localhost/JuseaCMN_v2/deployer_actuaciones.php
 *
 * El script es idempotente: verifica si la tabla ya existe antes de crearla.
 */

// ─── Configuración de base de datos ───────────────────────────────────────────
$host   = '127.0.0.1';
$dbname = 'jusea_cmn';
$user   = 'root';
$pass   = '';
$charset = 'utf8mb4';

// ─── Conexión PDO ─────────────────────────────────────────────────────────────
try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('<p style="color:red">❌ Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

$log = [];

// ─── 1. Crear tabla actuaciones ───────────────────────────────────────────────
$tablaExiste = $pdo->query("SHOW TABLES LIKE 'actuaciones'")->fetchColumn();

if ($tablaExiste) {
    $log[] = '⚠️  La tabla <code>actuaciones</code> ya existe — sin cambios.';
} else {
    $sql = "
        CREATE TABLE `actuaciones` (
            `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tipo`              VARCHAR(30)  NOT NULL COMMENT 'bienes | accidente',
            `datos`             JSON         NOT NULL COMMENT 'Todos los campos del formulario en JSON',
            `nro_expediente`    VARCHAR(50)  NULL DEFAULT NULL,
            `causante_apellido` VARCHAR(100) NULL DEFAULT NULL,
            `causante_grado`    VARCHAR(80)  NULL DEFAULT NULL,
            `estado`            VARCHAR(20)  NOT NULL DEFAULT 'activa'
                                COMMENT 'activa | cerrada | anulada',
            `created_by`        INT UNSIGNED NULL DEFAULT NULL
                                COMMENT 'FK a tabla usuarios (referencia suave)',
            `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_tipo`              (`tipo`),
            INDEX `idx_nro_expediente`    (`nro_expediente`),
            INDEX `idx_causante_apellido` (`causante_apellido`),
            INDEX `idx_estado`            (`estado`),
            INDEX `idx_created_by`        (`created_by`)
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
          COMMENT='Actuaciones administrativas (bienes y accidente/enfermedad)';
    ";
    $pdo->exec($sql);
    $log[] = '✅ Tabla <code>actuaciones</code> creada correctamente.';
}

// ─── 2. Verificar columnas clave ──────────────────────────────────────────────
$columnas = $pdo->query("SHOW COLUMNS FROM `actuaciones`")->fetchAll();
$nombresColumnas = array_column($columnas, 'Field');
$log[] = '📋 Columnas presentes: <code>' . implode('</code>, <code>', $nombresColumnas) . '</code>';

// ─── Salida HTML ──────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Deployer — Tabla actuaciones</title>
    <style>
        body { font-family: monospace; padding: 2rem; background: #f8f9fa; }
        h2   { color: #6b0f1a; }
        ul   { list-style: none; padding: 0; }
        li   { margin: .4rem 0; font-size: 1rem; }
        a    { color: #6b0f1a; }
    </style>
</head>
<body>
<h2>JUSEA CMN v2.0 — Deployer: Tabla actuaciones</h2>
<ul>
    <?php foreach ($log as $linea): ?>
    <li><?= $linea ?></li>
    <?php endforeach; ?>
</ul>
<hr>
<p>
    ✔ Proceso completado. Puede volver al
    <a href="/JuseaCMN_v2/public/dashboard">Panel Principal</a>.
</p>
<p style="color:#888;font-size:.85rem;">
    ⚠️ Por seguridad, elimine o renombre este archivo luego de ejecutarlo.
</p>
</body>
</html>

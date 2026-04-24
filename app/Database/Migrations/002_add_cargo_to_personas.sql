-- ============================================================
-- JUSEA CMN v2.0 — Migración 002
-- Agrega columna `cargo` a la tabla `personas`
-- Ejecutar UNA SOLA VEZ en phpMyAdmin o consola MySQL/MariaDB
-- Compatible: MySQL 8+ / MariaDB 10.3+
-- ============================================================

SET NAMES utf8mb4;

-- Agrega cargo si no existe (IF NOT EXISTS disponible en MariaDB 10.3+ y MySQL 8+)
ALTER TABLE `personas`
    ADD COLUMN IF NOT EXISTS `cargo` VARCHAR(150) DEFAULT NULL
    AFTER `destino_interno`;

-- Verificar
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personas'
-- ORDER BY ORDINAL_POSITION;

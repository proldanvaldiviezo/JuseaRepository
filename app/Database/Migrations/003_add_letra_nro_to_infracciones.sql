-- ============================================================
-- JUSEA CMN v2.0 — Migración 003
-- Agrega columnas `letra` y `nro` a tabla `infracciones`.
-- Necesarias para el encabezado "Letra / Nro." en la planilla
-- de sanción (ANEXO 1) y la planilla de revisión (ANEXO 2).
-- ============================================================

ALTER TABLE `infracciones`
    ADD COLUMN IF NOT EXISTS `letra` VARCHAR(20) DEFAULT '' AFTER `lugar_cumplimiento`,
    ADD COLUMN IF NOT EXISTS `nro`   VARCHAR(30) DEFAULT '' AFTER `letra`;

-- Verificar resultado
DESCRIBE `infracciones`;

-- ============================================================
-- Migración 004 — Tabla de Trazabilidad de Expedientes
-- JuseaCMN v2 — División Jurídica CMN
-- Ejecutar en MySQL/phpMyAdmin sobre la base de datos juseacmn
-- ============================================================

CREATE TABLE IF NOT EXISTS `trazabilidad` (
    `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Identificación del expediente origen
    `tipo_expediente`    ENUM(
                            'sancion_cuadros',
                            'sancion_cadetes',
                            'actuacion_bienes',
                            'actuacion_accidente'
                         ) NOT NULL,
    `id_expediente`      INT UNSIGNED NOT NULL,          -- PK de sanciones.id o actuaciones.id
    `nro_expediente`     VARCHAR(120) NOT NULL DEFAULT '', -- denormalizado para búsqueda rápida

    -- Área actual en posesión del expediente
    `area`               VARCHAR(80)  NOT NULL,           -- clave: 'personal', 'sanidad', etc.
    `area_label`         VARCHAR(150) NOT NULL DEFAULT '', -- etiqueta legible

    -- Estado del paso
    `estado_paso`        ENUM(
                            'en_tramite',
                            'recibido',
                            'elevado',
                            'devuelto',
                            'suspendido',
                            'resuelto',
                            'archivado'
                         ) NOT NULL DEFAULT 'en_tramite',

    -- Responsable en esta área
    `id_responsable`     INT UNSIGNED NULL,               -- FK opcional a personas.id
    `responsable_nombre` VARCHAR(200) NOT NULL DEFAULT '', -- nombre libre (grado + apellido)
    `responsable_cargo`  VARCHAR(200) NOT NULL DEFAULT '', -- cargo libre

    -- Tiempos
    `fecha_entrada`      DATETIME NOT NULL,               -- cuando llegó a esta área
    `fecha_salida`       DATETIME NULL,                   -- cuando pasó a la siguiente (NULL = paso activo)

    -- Control
    `activo`             TINYINT(1)  NOT NULL DEFAULT 1,  -- 1 = paso vigente para este expediente
    `observaciones`      TEXT,
    `created_by`         INT UNSIGNED NULL,
    `created_at`         DATETIME NULL,
    `updated_at`         DATETIME NULL,

    -- Índices
    KEY `idx_exp`        (`tipo_expediente`, `id_expediente`),
    KEY `idx_area`       (`area`, `activo`),
    KEY `idx_activo`     (`activo`, `estado_paso`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

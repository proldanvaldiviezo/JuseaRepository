-- ============================================================
-- JUSEA CMN v2.0 - Migración de Base de Datos
-- Sistema de Tramitación de Expedientes - Colegio Militar de la Nación
-- Fecha: 2026-03-20
-- Motor: MySQL 8+ / MariaDB 10.5+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------
-- 1. TABLA DE USUARIOS (autenticación y roles)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `nombre_completo` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `rol` ENUM('admin', 'operador') NOT NULL DEFAULT 'operador',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `ultimo_acceso` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario admin inicial (password: Admin2026!)
INSERT INTO `usuarios` (`username`, `password_hash`, `nombre_completo`, `rol`) VALUES
('admin', '$2y$10$placeholder_hash_change_on_first_login', 'Administrador del Sistema', 'admin');

-- -----------------------------------------------------------
-- 2. TABLA DE ENCABEZADO INSTITUCIONAL
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `encabezado` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `anio` VARCHAR(4) NOT NULL DEFAULT '2026',
    `membrete` VARCHAR(255) NOT NULL DEFAULT 'Año del Bicentenario',
    `unidad` VARCHAR(255) NOT NULL DEFAULT 'Colegio Militar de la Nación',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` INT UNSIGNED DEFAULT NULL,
    CONSTRAINT `fk_encabezado_usuario` FOREIGN KEY (`updated_by`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `encabezado` (`anio`, `membrete`, `unidad`) VALUES
('2026', 'Año del Bicentenario de la Independencia', 'Colegio Militar de la Nación "Tte Grl Pablo Riccheri"');

-- -----------------------------------------------------------
-- 3. TABLA DE PERSONAS (infractores, instructores, autoridades)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `personas` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dni` VARCHAR(10) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `nombre` VARCHAR(100) NOT NULL,
    `grado` VARCHAR(80) DEFAULT NULL,
    `arma_especialidad` VARCHAR(100) DEFAULT NULL,
    `destino_interno` VARCHAR(150) DEFAULT NULL,
    `tipo` ENUM('cuadro', 'cadete', 'instructor', 'autoridad', 'civil') NOT NULL DEFAULT 'cuadro',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_dni` (`dni`),
    INDEX `idx_apellido` (`apellido`),
    INDEX `idx_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 4. TABLA DE INFRACCIONES
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `infracciones` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `fecha_comision` DATE NOT NULL,
    `reg_act_dis` VARCHAR(100) DEFAULT NULL COMMENT 'Reglamento de Actividad Disciplinaria',
    `inciso` VARCHAR(50) DEFAULT NULL,
    `motivo` TEXT NOT NULL,
    `tipo_sancion` VARCHAR(100) DEFAULT NULL,
    `duracion` VARCHAR(100) DEFAULT NULL,
    `lugar_cumplimiento` VARCHAR(200) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 5. TABLA DE SANCIONES (vincula persona + infracción + autoridad)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sanciones` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_infraccion` INT UNSIGNED NOT NULL,
    `id_causante` INT UNSIGNED NOT NULL COMMENT 'Persona infractora',
    `id_autoridad` INT UNSIGNED NOT NULL COMMENT 'Persona que impone',
    `tipo_sancion` ENUM('cuadros', 'cadetes') NOT NULL DEFAULT 'cuadros',
    `estado` ENUM('activa', 'cumplida', 'anulada') NOT NULL DEFAULT 'activa',
    `observaciones` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sancion_infraccion` FOREIGN KEY (`id_infraccion`) REFERENCES `infracciones`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_sancion_causante` FOREIGN KEY (`id_causante`) REFERENCES `personas`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_sancion_autoridad` FOREIGN KEY (`id_autoridad`) REFERENCES `personas`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_sancion_usuario` FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_tipo_sancion` (`tipo_sancion`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 6. TABLA DE NOTAS OBJETO (elevación de expedientes)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notas_objeto` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `expediente_letra` VARCHAR(10) NOT NULL,
    `expediente_numero` VARCHAR(20) NOT NULL,
    `destinatario_grado` VARCHAR(80) NOT NULL,
    `destinatario_arma` VARCHAR(100) NOT NULL,
    `destinatario_apellido` VARCHAR(100) NOT NULL,
    `destinatario_nombre` VARCHAR(100) NOT NULL,
    `afectado_grado` VARCHAR(80) NOT NULL,
    `afectado_arma` VARCHAR(100) NOT NULL,
    `afectado_apellido` VARCHAR(100) NOT NULL,
    `afectado_nombre` VARCHAR(100) NOT NULL,
    `afectado_dni` VARCHAR(10) NOT NULL,
    `texto_elevacion` TEXT DEFAULT NULL,
    `fecha_documento` DATE NOT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_nota_usuario` FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 7. TABLA DE EXPEDIENTES POR BIENES DEL ESTADO
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expedientes_bienes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `numero_expediente` VARCHAR(50) NOT NULL,
    `fecha_apertura` DATE NOT NULL,
    `tipo_bien` VARCHAR(100) NOT NULL COMMENT 'Material, equipo, armamento, etc.',
    `descripcion_bien` TEXT NOT NULL,
    `valor_estimado` DECIMAL(12,2) DEFAULT NULL,
    `responsable_id` INT UNSIGNED DEFAULT NULL COMMENT 'Persona responsable del bien',
    `instructor_id` INT UNSIGNED DEFAULT NULL COMMENT 'Instructor de la actuación',
    `dependencia` VARCHAR(200) DEFAULT NULL,
    `causa_perdida` TEXT NOT NULL COMMENT 'Circunstancias de la pérdida/daño',
    `estado` ENUM('en_tramite', 'resuelto', 'archivado', 'elevado') NOT NULL DEFAULT 'en_tramite',
    `resolucion` TEXT DEFAULT NULL,
    `fecha_resolucion` DATE DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_bienes_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `personas`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_bienes_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `personas`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_bienes_usuario` FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_estado_bien` (`estado`),
    INDEX `idx_numero_exp` (`numero_expediente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 8. TABLA DE DOCUMENTOS GENERADOS (auditoría)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `documentos_generados` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tipo_documento` ENUM('sancion_cuadros', 'sancion_cadetes', 'nota_objeto', 'bienes_estado') NOT NULL,
    `referencia_id` INT UNSIGNED NOT NULL COMMENT 'ID del registro origen',
    `formato` ENUM('docx', 'pdf') NOT NULL,
    `nombre_archivo` VARCHAR(255) NOT NULL,
    `generado_por` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_doc_usuario` FOREIGN KEY (`generado_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

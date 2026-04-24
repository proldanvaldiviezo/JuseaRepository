<?php

/**
 * JUSEA CMN v2.0 - Bootstrap para entorno PRODUCTION
 * CodeIgniter 4
 */

// Ocultar errores en produccion
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

// Desactivar debug de CI4
defined('CI_DEBUG') || define('CI_DEBUG', false);

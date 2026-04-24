<?php

/**
 * JUSEA CMN v2.0 - Bootstrap para entorno DEVELOPMENT
 * CodeIgniter 4
 */

// Mostrar todos los errores en desarrollo
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Activar debug de CI4
defined('CI_DEBUG') || define('CI_DEBUG', true);

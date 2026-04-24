<?php

namespace Config;

/**
 * Paths - Rutas del sistema JUSEA CMN v2.0
 *
 * Define las rutas a los directorios principales
 * de la aplicacion CodeIgniter 4.
 */
class Paths
{
    /**
     * Directorio de la aplicacion (Models, Controllers, Views, etc.)
     */
    public string $appDirectory = __DIR__ . '/..';

    /**
     * Directorio del framework CodeIgniter (vendor)
     */
    public string $systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system';

    /**
     * Directorio writable (logs, cache, session, uploads)
     */
    public string $writableDirectory = __DIR__ . '/../../writable';

    /**
     * Directorio de tests (no usado en produccion)
     */
    public string $testsDirectory = __DIR__ . '/../../tests';

    /**
     * Directorio de vistas
     */
    public string $viewDirectory = __DIR__ . '/../Views';
    public string $envDirectory = __DIR__ . '/../../';
}

<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * Autoload - Carga automatica de clases y helpers
 */
class Autoload extends AutoloadConfig
{
    /**
     * Namespaces del PSR-4
     */
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
    ];

    /**
     * Classmap (clases sin namespace)
     */
    public $classmap = [];

    /**
     * Archivos que se cargan en cada request
     */
    public $files = [];

    /**
     * Helpers que se cargan automaticamente
     */
    public $helpers = ['url', 'form', 'text'];
}

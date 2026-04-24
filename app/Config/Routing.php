<?php

namespace Config;

use CodeIgniter\Config\Routing as BaseRouting;

class Routing extends BaseRouting
{
    /**
     * Default namespace for Controllers.
     */
    public string $defaultNamespace = 'App\Controllers';

    /**
     * Default controller
     */
    public string $defaultController = 'Home';

    /**
     * Default method
     */
    public string $defaultMethod = 'index';

    /**
     * Translate dashes in URIs to underscores
     */
    public bool $translateURIDashes = false;

    /**
     * Sets the 404 Override
     */
    public ?string $override404 = null;

    /**
     * Auto-route
     */
    public bool $autoRoute = false;

    /**
     * Route files
     */
    public array $routeFiles = [
        APPPATH . 'Config/Routes.php',
    ];

    /**
     * Module route files
     */
    public array $moduleRoutes = [];

    /**
     * Priority routes
     */
    public bool $prioritize = false;
    public bool $useControllerAttributes = true;
    public bool $multipleSegmentsOneParam = false;
    public bool $translateUriToCamelCase = true;
}

<?php

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

class Modules extends BaseModules
{
    /**
     * Should the application auto-discover the requested resources.
     */
    public $enabled = true;

    /**
     * Aliases for auto-discovery
     */
    public $aliases = [];

    /**
     * Composer packages to auto-discover
     */
    public $composerPackages = [];

    /**
     * Auto-discovery within the application directory
     */
    public $discoverInComposer = true;
}

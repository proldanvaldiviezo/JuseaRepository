<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Feature extends BaseConfig
{
    /**
     * Use improved new auto routing instead of the default legacy version.
     */
    public bool $autoRoutesImproved = false;

    /**
     * Use filter execution order in globals, methods, filters.
     */
    public bool $multipleFilters = true;

    /**
     * Use improved new Placeholders
     */
    public bool $limitZeroAsAll = true;
    public bool $oldFilterOrder = false;
    public bool $strictLocaleNegotiation = false;
}

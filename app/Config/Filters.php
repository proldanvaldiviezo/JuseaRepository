<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\AuthFilter;
use App\Filters\RoleFilter;

/**
 * Configuracion de filtros HTTP para JUSEA CMN v2.0
 */
class Filters extends BaseFilters
{
    /**
     * Alias de filtros disponibles.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'auth'          => AuthFilter::class,
        'role'          => RoleFilter::class,
    ];

    /**
     * Filtros obligatorios (CI4 4.6+).
     */
    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    /**
     * Filtros que se aplican ANTES de cada request.
     */
    public array $globals = [
        'before' => [
            // 'csrf',
        ],
        'after' => [
            'toolbar',
        ],
    ];

    /**
     * Filtros por metodo HTTP.
     */
    public array $methods = [];

    /**
     * Filtros por URI especifica.
     */
    public array $filters = [];
}

<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuracion general de la aplicacion JUSEA CMN v2.0
 */
class App extends BaseConfig
{
    /**
     * URL base del sitio.
     */
    public string $baseURL = 'http://jusea.local/';

    /**
     * Caracteres permitidos en URIs
     */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * URI Protocol - como detectar la URI
     * REQUEST_URI, QUERY_STRING, PATH_INFO
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * Index page - dejar vacio si se usa mod_rewrite
     */
    public string $indexPage = '';

    /**
     * Locale por defecto
     */
    public string $defaultLocale = 'es';

    public bool $negotiateLocale = false;

    public array $supportedLocales = ['es'];

    /**
     * Zona horaria de la aplicacion
     */
    public string $appTimezone = 'America/Argentina/Buenos_Aires';

    /**
     * Charset de la aplicacion
     */
    public string $charset = 'UTF-8';

    /**
     * Forzar HTTPS global
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * Proxy IPs
     */
    public array $proxyIPs = [];

    /**
     * Content Security Policy
     */
    public bool $CSPEnabled = false;

    /**
     * Hostnames permitidos — requerido por CodeIgniter 4.4+
     * Array vacio = acepta cualquier hostname (comportamiento equivalente al anterior).
     */
    public array $allowedHostnames = [];
}

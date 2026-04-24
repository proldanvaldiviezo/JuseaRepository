<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Throwable;

class Exceptions extends BaseConfig
{
    /**
     * If true, a log entry will be made for each exception.
     */
    public bool $log = true;

    /**
     * If true, a backtrace will be displayed in log messages.
     */
    public bool $logBacktrace = false;

    /**
     * Path to the directory that holds the error view files.
     */
    public string $errorViewPath = APPPATH . 'Views/errors';

    /**
     * --------------------------------------------------------------------------
     * DEPRECATED PROPERTIES
     * --------------------------------------------------------------------------
     */
    public array $sensitiveDataInTrace = [];

    /**
     * HTTP status codes that are ignored and not logged.
     */
    public array $ignoreCodes = [404];
    public bool $logDeprecations = true;
    public string $deprecationLogLevel = 'warning';

    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        return new ExceptionHandler($this);
    }
}

<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;

class Logger extends BaseConfig
{
    /**
     * Error Logging Threshold
     * 0 = Disables logging, Error logging TURNED OFF
     * 1 = Emergency Messages
     * 2 = Alert Messages
     * 3 = Critical Messages
     * 4 = Runtime Errors
     * 5 = Warnings
     * 6 = Notices
     * 7 = Informational Messages
     * 8 = Debug Messages
     * 9 = All Messages
     */
    public $threshold = 4;

    public $dateFormat = 'Y-m-d H:i:s';

    public $handlers = [
        FileHandler::class => [
            'handles' => [
                'critical', 'alert', 'emergency', 'debug',
                'error', 'info', 'notice', 'warning',
            ],
            'filePermissions' => 0644,
        ],
    ];
}

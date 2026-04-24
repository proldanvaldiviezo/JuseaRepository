<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ContentSecurityPolicy extends BaseConfig
{
    public bool $reportOnly = false;
    public string $defaultSrc = 'none';
    public string $scriptSrc = 'self';
    public string $styleSrc = 'self';
    public string $imgSrc = 'self';
    public ?string $baseURI = null;
    public ?string $childSrc = null;
    public string $connectSrc = 'self';
    public ?string $fontSrc = null;
    public ?string $formAction = null;
    public ?string $frameAncestors = null;
    public ?string $frameSrc = null;
    public ?string $mediaSrc = null;
    public ?string $objectSrc = null;
    public ?string $pluginTypes = null;
    public ?string $reportURI = null;
    public bool $sandbox = false;
    public bool $upgradeInsecureRequests = false;
    public string $styleNonceTag = '{csp-style-nonce}';
    public string $scriptNonceTag = '{csp-script-nonce}';
    public bool $autoNonce = true;
    public ?string $reportTo = null;
    public array|string $scriptSrcElem = 'self';
    public array|string $scriptSrcAttr = 'self';
    public array|string $styleSrcElem = 'self';
    public array|string $styleSrcAttr = 'self';
    public array|string $workerSrc = [];
}

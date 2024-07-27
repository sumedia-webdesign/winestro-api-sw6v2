<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Service\Wbo\Url;

use Sumedia\WinestroApi\Config;

class ShopUrl implements UrlInterface {

    protected string $path = 'wbo-API.php';

    public function __construct(private Config $config)
    {

    }

    public function getUrlPath(): string
    {
        return $this->path;
    }

    public function getUrlParams(): array
    {
        return array();
    }
}

<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi\Service\Wbo\Request;

use Sumedia\WinestroApi\Service\Wbo\Url\UrlInterface;

interface RequestInterface
{
    public function getApiAction(): string;
    public function getResponseClass(): string;
    public function getUrl(): UrlInterface;
    public function getUrlParams(): array;
}
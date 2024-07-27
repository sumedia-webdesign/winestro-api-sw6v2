<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi\Service\Wbo\Url;

interface UrlInterface
{
    public function getUrlPath() : string;
    public function getUrlParams() : array;
}

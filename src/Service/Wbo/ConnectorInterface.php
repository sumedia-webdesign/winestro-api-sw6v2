<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi\Service\Wbo;

use Sumedia\WinestroApi\Service\Wbo\Request\RequestInterface;
use Sumedia\WinestroApi\Service\Wbo\Response\ResponseInterface;

interface ConnectorInterface
{
    public function execute(RequestInterface $request): ResponseInterface;
}
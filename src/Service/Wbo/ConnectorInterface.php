<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroAPI\Service\Wbo;

use Sumedia\WinestroAPI\Service\Wbo\Request\RequestInterface;
use Sumedia\WinestroAPI\Service\Wbo\Response\ResponseInterface;

interface ConnectorInterface
{
    public function execute(RequestInterface $request): ResponseInterface;
}
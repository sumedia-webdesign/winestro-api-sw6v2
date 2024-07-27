<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

use Sumedia\WinestroApi\Winestro\Request\RequestInterface;
use Sumedia\WinestroApi\Winestro\Response\ResponseInterface;

interface ConnectionInterface
{
    public function setParameter(string $key, string $value): void;
    public function getParameter(string $key): string;
    public function hasParameter(string $key): bool;
    public function removeParameter(string $key): void;
    public function getParameters(): array;
    public function setUrl(string $url): void;
    public function executeRequest(RequestInterface $request): ResponseInterface;
}
<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Request;

use Sumedia\WinestroApi\Winestro\Response\ResponseInterface;

interface RequestInterface
{
    public function setParameter(string $key, string $value): void;
    public function getParameter(string $key): mixed;
    public function removeParameter(string $key): void;
    public function hasParameter(string $key): bool;
    public function getParameters(): array;
    public function getResponseName(): string;
}
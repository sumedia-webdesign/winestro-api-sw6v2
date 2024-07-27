<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Service\Wbo\Response;

interface ResponseInterface
{
    public function get(string $key);
    public function getData(): array;
    public function isSuccessful(): bool;
    public function getError(): ?string;
}

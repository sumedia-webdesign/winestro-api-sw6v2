<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi;

interface ConfigInterface
{
    public function set(string $key, mixed $value): void;
    public function get(string $key): mixed;
    public function toArray(): array;
}
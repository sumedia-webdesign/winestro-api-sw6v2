<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\DataMapper;

interface DataMapperInterface
{
    public function getConstants(): array;
    public function mapKey(string $key): mixed;
    public function toArray(): array;
}

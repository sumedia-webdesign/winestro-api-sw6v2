<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\DataMapper;

interface DataMapperInterface
{
    public function mapKey(string $key): string;
    public function toArray(): array;
}

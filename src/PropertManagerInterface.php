<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupEntity;

interface PropertyManagerInterface
{
    public function getPropertyGroupByMappingKey(string $mappingKey): ?PropertyGroupEntity;
    public function getOrCreatePropertyGroupOptionByMappingKey(string $mappingKey, mixed $value): string;
    public function getPropertyGroupOptionByMappingKey(string $mappingKey, mixed $value): ?PropertyGroupOptionEntity;
}

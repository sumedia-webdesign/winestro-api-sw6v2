<?php

declare(strict_types=1);

namespace Sumedia\WinestroApi\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class WboProductsCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return WboProductsEntity::class;
    }
}

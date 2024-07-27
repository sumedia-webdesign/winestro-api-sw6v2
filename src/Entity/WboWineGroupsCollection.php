<?php

declare(strict_types=1);

namespace Sumedia\WinestroApi\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class WboWineGroupsCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return WboWineGroupsEntity::class;
    }
}

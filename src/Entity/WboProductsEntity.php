<?php

declare(strict_types=1);

namespace Sumedia\WinestroApi\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class WboProductsEntity extends Entity
{
    use EntityIdTrait;

    /** @var string */
    protected $productId;

    /** @var string */
    protected $wboArticleId;

    public function getProductId() : string
    {
        return $this->productId;
    }

    public function setProductId(string $productId) : void
    {
        $this->productId = $productId;
    }

    public function getWboArticleId(): int
    {
        return (int)$this->wboArticleId;
    }

    public function setWboArticleId(int $wboArticleId): void
    {
        $this->wboArticleId = $wboArticleId;
    }
}


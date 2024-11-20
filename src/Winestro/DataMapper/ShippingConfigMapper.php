<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\DataMapper;

class ShippingConfigMapper implements DataMapperInterface
{
    const SHIPPING_COLLECT = 3;
    const SHIPPING_TOUR = 6;
    const SHIPPING_DPD = 9;
    const SHIPPING_DHL = 12;
    const SHIPPING_UPS = 15;
    const SHIPPING_TOPLOGISTIK = 18;
    const SHIPPING_GLS = 21;
    const SHIPPING_HAULAGE = 24;
    const SHIPPING_NONE = 27;
    const SHIPPING_BY_CONTACT = 29;
    const SHIPPING_HERMES = 28;
    const SHIPPING_EXW_MERTESDORF = 32;
    const SHIPPING_FCA_MERTESDORF = 33;
    const SHIPPING_IHV_SPEDITION = 31;

    private array $map = [];

    public function __construct()
    {
        foreach ($this->getConstants() as $key => $value) {
            $this->map[$key] = $value;
        }
    }

    public function getConstants(): array
    {
        $ref = new \ReflectionClass(self::class);
        return (array) $ref->getConstants();
    }

    public function mapKey(string $key): mixed
    {
        return $this->map[$key];
    }

    public function toArray(): array
    {
        return $this->map;
    }
}

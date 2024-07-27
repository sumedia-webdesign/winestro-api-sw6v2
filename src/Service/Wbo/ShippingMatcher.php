<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Service\Wbo;

use Sumedia\WinestroApi\Config\WboConfig;
use Sumedia\WinestroApi\Service\ShippingConfigMapper;
use Sumedia\WinestroApi\Service\WboShippings;

class ShippingMatcher
{
    const WBO_SHIPPING_FALLBACK_ID = 0;

    protected WboConfig $wboConfig;
    protected array $shippingConfigMappingConstants;

    public function __construct(WboConfig $wboConfig)
    {
        $this->wboConfig = $wboConfig;

        $reflection = new \ReflectionClass(ShippingConfigMapper::class);
        /** @var \ReflectionClassConstant $constant */
        $this->shippingConfigMappingConstants = $reflection->getConstants();
    }

    public function getShippingIds(): array
    {
        $shippingIds = [];
        foreach ($this->shippingConfigMappingConstants as $constant) {
            $shippingId = $this->wboConfig->get($constant);
            if (!$shippingId) {
                continue;
            }
            $camelCaseToWord = preg_split('/(?=[A-Z])/', $constant);
            $camelCaseToWord = array_map(function($a) {
                return strtoupper($a);
            }, $camelCaseToWord);
            $identifier = str_replace('_MAPPING', '', implode('_', $camelCaseToWord));
            $shippingIds[$shippingId] = constant(WboShippings::class . '::' . $identifier);
        }
        return $shippingIds;
    }
}

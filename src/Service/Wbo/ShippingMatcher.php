<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroAPI\Service\Wbo;

use Sumedia\WinestroAPI\Config\WboConfig;
use Sumedia\WinestroAPI\Service\ShippingConfigMapping;
use Sumedia\WinestroAPI\Service\WboShippings;

class ShippingMatcher
{
    const WBO_SHIPPING_FALLBACK_ID = 0;

    protected WboConfig $wboConfig;
    protected array $shippingConfigMappingConstants;

    public function __construct(WboConfig $wboConfig)
    {
        $this->wboConfig = $wboConfig;

        $reflection = new \ReflectionClass(ShippingConfigMapping::class);
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

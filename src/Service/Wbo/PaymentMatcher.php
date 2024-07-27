<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Service\Wbo;

use Sumedia\WinestroApi\Config\WboConfig;
use Sumedia\WinestroApi\Service\PaymentConfigMapping;
use Sumedia\WinestroApi\Service\WboPayments;

class PaymentMatcher
{
    const WBO_PAYMENT_FALLBACK_ID = WboPayments::PAYMENT_PREPAYED;

    protected WboConfig $wboConfig;
    protected array $paymentConfigMappingConstants;

    public function __construct(WboConfig $wboConfig)
    {
        $this->wboConfig = $wboConfig;

        $reflection = new \ReflectionClass(PaymentConfigMapping::class);
        /** @var \ReflectionClassConstant $constant */
        $this->paymentConfigMappingConstants = $reflection->getConstants();
    }

    public function getPaymentIds(): array
    {
        $paymentIds = [];
        foreach ($this->paymentConfigMappingConstants as $constant) {
            $paymentId = $this->wboConfig->get($constant);
            if (!$paymentId) {
                continue;
            }
            $camelCaseToWord = preg_split('/(?=[A-Z])/', $constant);
            $camelCaseToWord = array_map(function($a) {
                return strtoupper($a);
            }, $camelCaseToWord);
            $identifier = str_replace('_MAPPING', '', implode('_', $camelCaseToWord));
            $paymentIds[$paymentId] = constant(WboPayments::class . '::' . $identifier);
        }
        return $paymentIds;
    }
}
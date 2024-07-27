<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Service\Wbo\Request;

use Sumedia\WinestroApi\Service\Wbo\Response\CheckOrderStatus as CheckOrderStatusResponse;

class CheckOrderStatus extends RequestAbstract
{
    protected string $apiAction = 'getAuftragStatus';
    protected string $responseClass = CheckOrderStatusResponse::class;

    public function setOrderNumber(string $orderNumber): void
    {
        $this->set('auftragnummer', $orderNumber);
    }
}

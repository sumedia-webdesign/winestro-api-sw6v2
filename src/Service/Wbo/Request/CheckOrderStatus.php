<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroAPI\Service\Wbo\Request;

use Sumedia\WinestroAPI\Service\Wbo\Response\CheckOrderStatus as CheckOrderStatusResponse;

class CheckOrderStatus extends RequestAbstract
{
    protected string $apiAction = 'getAuftragStatus';
    protected string $responseClass = CheckOrderStatusResponse::class;

    public function setOrderNumber(string $orderNumber): void
    {
        $this->set('auftragnummer', $orderNumber);
    }
}

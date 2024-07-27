<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Request;

use Sumedia\WinestroApi\Winestro\RequestManager;

class GetOrderStatusFromWinestroRequest extends AbstractRequest
{
    private string $responseName = RequestManager::GET_ORDER_STATUS_FROM_WINESTRO_RESPONSE;

    public function __construct()
    {
        $this->setParameter('apiACTION', 'getAuftragStatus');
    }

    public function getResponseName(): string
    {
        return $this->responseName;
    }
}

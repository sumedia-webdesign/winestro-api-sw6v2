<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Request;

use Sumedia\WinestroApi\Winestro\RequestManager;

class SendOrderToWinestroRequest extends AbstractRequest
{
    private string $responseName = RequestManager::SEND_ORDER_TO_WINESTRO_RESPONSE;

    public function __construct()
    {
        $this->setParameter('apiACTION', 'newOrder');
    }

    public function getResponseName(): string
    {
        return $this->responseName;
    }
}

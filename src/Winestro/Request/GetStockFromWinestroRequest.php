<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Request;

use Sumedia\WinestroApi\Winestro\RequestManager;

class GetStockFromWinestroRequest extends AbstractRequest
{
    private string $responseName = RequestManager::GET_STOCK_FROM_WINESTRO_RESPONSE;

    public function __construct()
    {
        $this->setParameter('apiACTION', 'getBestand');
    }

    public function getResponseName(): string
    {
        return $this->responseName;
    }
}

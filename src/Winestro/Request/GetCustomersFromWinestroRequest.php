<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Request;

use Sumedia\WinestroApi\Winestro\RequestManager;

class GetCustomersFromWinestroRequest extends AbstractRequest
{
    private string $responseName = RequestManager::GET_CUSTOMERS_FROM_WINESTRO_RESPONSE;

    public function __construct()
    {
        $this->setParameter('apiACTION', 'getKundenGruppe');
    }

    public function getResponseName(): string
    {
        return $this->responseName;
    }
}

<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Request;

use Sumedia\WinestroApi\Winestro\RequestManager;

class GetArticlesFromWinestroRequest extends AbstractRequest
{
    private string $responseName = RequestManager::GET_ARTICLES_FROM_WINESTRO_RESPONSE;

    public function __construct()
    {
        $this->setParameter('apiACTION', 'getArtikel');
    }

    public function getResponseName(): string
    {
        return $this->responseName;
    }
}

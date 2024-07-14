<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroAPI\Service\Wbo\Request;

use Sumedia\WinestroAPI\Config\WboConfig;
use Sumedia\WinestroAPI\Service\Wbo\Response\GetBestand as GetBestandResponse;

class GetBestand extends RequestAbstract
{
    protected string $apiAction = 'getBestand';
    protected string $responseClass = GetBestandResponse::class;

    public function __construct(
        WboConfig $wboConfig,
        string $apiAction = null,
        string $responseClass = null,
        string $urlClass = null
    ) {
        parent::__construct($wboConfig, $apiAction, $responseClass, $urlClass);
        $this->set('reservierung', 'true');
    }

    public function setArticleNr(string $articleNr): void
    {
        $this->set('artikelnr', $articleNr);
    }
}

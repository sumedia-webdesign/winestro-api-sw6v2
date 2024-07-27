<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Response;

use Sumedia\WinestroApi\Winestro\DataMapper\WinestroArticleDataMapper;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;

class GetStockFromWinestroResponse extends AbstractResponse
{
    public function populate(array $data): void
    {
        if (!isset($data['item'])) {
            throw new NoEntriesException('no data for this item');
        }
        $this->data['stock'] = $data['item']['artikel_bestand'];
    }
}

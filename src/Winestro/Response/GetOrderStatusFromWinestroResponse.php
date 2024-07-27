<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Response;

use Sumedia\WinestroApi\Winestro\DataMapper\WinestroArticleDataMapper;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;

class GetOrderStatusFromWinestroResponse extends AbstractResponse
{
    public function populate(array $data): void
    {
        if (!isset($data['item'])) {
            throw new NoEntriesException('no data for this item');
        }
        $this->data = [
            'status' => $data['item']['auftrag_status'],
            'link' => $data['item']['auftrag_versandlink'],
            'billingNumber' => $data['item']['auftrag_rechnungsnummer'],
            'payedStatus' => $data['item']['auftrag_bezahlt']
        ];
    }
}

<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Response;

use Sumedia\WinestroApi\Winestro\DataMapper\WinestroArticleDataMapper;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;

class SendOrderToWinestroResponse extends AbstractResponse
{
    public function populate(array $data): void
    {
        if (!isset($data['nr'])) {
            throw new InvalidOrderExportException('the order could not be sendet');
        }
        $data = ['orderNumber' => $data['nr']];
        parent::populate($data);
    }
}

<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Response;

use Sumedia\WinestroApi\Winestro\DataMapper\WinestroArticleDataMapper;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;

class GetCustomerGroupsFromWinestroResponse extends AbstractResponse
{
    public function populate(array $data): void
    {
        if (!isset($data['item']) && !isset($data['items'])) {
            throw new NoEntriesException('no data for this item');
        }
        $items = isset($data['items']) ? $data['items'] : [$data['item']];
        foreach ($items as $value)
        {
            $this->data[] = [
                'id' => $value['grp_nr'],
                'name' => $value['grp_name'],
                'description' => $value['grp_beschreibung'],
            ];
        }
    }
}

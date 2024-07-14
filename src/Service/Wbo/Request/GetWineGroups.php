<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroAPI\Service\Wbo\Request;

use Sumedia\WinestroAPI\Service\Wbo\Response\GetWineGroups as GetWineGroupsResponse;

class GetWineGroups extends RequestAbstract
{
    protected string $apiAction = 'getWineGroups';
    protected string $responseClass = GetWineGroupsResponse::class;
}

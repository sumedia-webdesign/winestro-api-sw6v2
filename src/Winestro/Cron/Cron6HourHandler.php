<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Sumedia\WinestroApi\Winestro\CronManagerInterface;
use Sumedia\WinestroApi\Winestro\TaskManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron6Hour::class)]
class Cron6HourHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron6Hour::class ];
    }

    public function run(): void
    {
        $this->_run('6h');
    }
}

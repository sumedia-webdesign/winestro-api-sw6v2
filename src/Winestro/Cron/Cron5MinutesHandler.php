<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron5Minutes::class)]
class Cron5MinutesHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron5Minutes::class ];
    }

    public function run(): void
    {
        $this->_run('5m');
    }
}

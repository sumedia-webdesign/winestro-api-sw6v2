<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron15Minutes::class)]
class Cron15MinutesHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron15Minutes::class ];
    }

    public function run(): void
    {
        $this->_run('15m');
    }
}

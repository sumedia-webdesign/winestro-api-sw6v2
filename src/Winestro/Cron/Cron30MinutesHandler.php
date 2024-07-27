<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron30Minutes::class)]
class Cron30MinutesHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron30Minutes::class ];
    }

    public function run(): void
    {
        $this->_run('30m');
    }
}

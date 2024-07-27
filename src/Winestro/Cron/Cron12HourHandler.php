<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron12Hour::class)]
class Cron12HourHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron12Hour::class ];
    }

    public function run(): void
    {
        $this->_run('12h');
    }
}

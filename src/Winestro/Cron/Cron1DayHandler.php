<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron1Day::class)]
class Cron1DayHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron1Day::class ];
    }

    public function run(): void
    {
        $this->_run('1d');
    }
}

<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron1Week::class)]
class Cron1WeekHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron1Week::class ];
    }

    public function run(): void
    {
        $this->_run('1w');
    }
}

<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron1Month::class)]
class Cron1MonthHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron1Month::class ];
    }

    public function run(): void
    {
        $this->_run('1m');
    }
}

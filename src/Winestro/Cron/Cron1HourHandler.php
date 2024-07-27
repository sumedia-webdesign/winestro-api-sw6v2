<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: Cron1Hour::class)]
class Cron1HourHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ Cron1Hour::class ];
    }

    public function run(): void
    {
        $this->_run('1h');
    }
}

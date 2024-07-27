<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class Cron12Hour extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'sumedia_winestro.cron_12_hour';
    }

    public static function getDefaultInterval(): int
    {
        return 43200;
    }
}

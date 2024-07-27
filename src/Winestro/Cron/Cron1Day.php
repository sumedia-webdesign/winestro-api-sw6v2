<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class Cron1Day extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'sumedia_winestro.cron_1_day';
    }

    public static function getDefaultInterval(): int
    {
        return 86400;
    }
}

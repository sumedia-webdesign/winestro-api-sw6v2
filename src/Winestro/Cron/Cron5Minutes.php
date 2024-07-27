<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class Cron5Minutes extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'sumedia_winestro.cron_5_minutes';
    }

    public static function getDefaultInterval(): int
    {
        return 300;
    }
}

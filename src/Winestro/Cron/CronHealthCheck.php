<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CronHealthCheck extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'sumedia_winestro.cron_health_check';
    }

    public static function getDefaultInterval(): int
    {
        return 60;
    }
}

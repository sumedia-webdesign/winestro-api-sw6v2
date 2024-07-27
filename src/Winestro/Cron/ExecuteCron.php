<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ExecuteCron extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'sumedia_winestro.execute_cron';
    }

    public static function getDefaultInterval(): int
    {
        return 60;
    }
}

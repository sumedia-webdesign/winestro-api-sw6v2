<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class Cron1Month extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'sumedia_winestro.cron_1_month';
    }

    public static function getDefaultInterval(): int
    {
        /** (365 * 4 + 1) / 4 / 12 * 24 * 60 * 60 */
        return 2629800;
    }
}

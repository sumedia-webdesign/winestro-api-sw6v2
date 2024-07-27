<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class UpdateArticles extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'wbo.update_articles';
    }

    public static function getDefaultInterval(): int
    {
        return 900;
    }
}

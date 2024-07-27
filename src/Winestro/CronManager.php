<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

use Sumedia\WinestroApi\ConfigInterface;

class CronManager implements CronManagerInterface
{
    protected $crons;

    public function __construct(
        private ConfigInterface $config
    ){
        $this->crons = $this->config->get('cron');
    }

    public function getTaskIdsByTime(string $time): array
    {
        $taskIds = array();
        if (!is_array($this->crons)) {
            return [];
        }
        foreach ($this->crons as $cron) {
            if ($cron['times'] === $time && $cron['enabled']['enabled']) {
                $taskIds[] = $cron['taskId'];
            }
        }
        return $taskIds;
    }
}
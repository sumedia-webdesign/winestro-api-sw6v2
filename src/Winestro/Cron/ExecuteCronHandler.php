<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: ExecuteCron::class)]
class ExecuteCronHandler extends AbstractCron
{
    public static function getHandledMessages(): iterable
    {
        return [ ExecuteCron::class ];
    }

    public function run(): void
    {
        $taskIds = $this->config->get('execute');
        if (null === $taskIds || !count($taskIds)) {
            return;
        }
        $this->config->set('execute', []);
        $this->logManager->newLogId();
        try {
            $this->logManager->logProcess("[cron run execute]");
            $this->logManager->logProcess('[cron] tasks ' . implode(',', $taskIds));
            foreach ($taskIds as $taskId) {
                $this->logManager->logProcess('[cron] run task ' . $taskId);
                $this->taskManager->getTask($taskId)->execute();
            }
            $this->logManager->logProcess('[cron success]');
        } catch (\Exception $e) {
            $this->logManager->logException($e);
            $this->logManager->logProcess('[cron message] ' .
                $e->getMessage() . ' in ' . $e->getFile() . ' on ' . $e->getLine() . ': ' . $e->getTraceAsString()
            );
            $this->logManager->logProcess('[cron failed]');
        }
    }
}

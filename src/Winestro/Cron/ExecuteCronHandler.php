<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Sumedia\WinestroApi\ConfigInterface;
use Sumedia\WinestroApi\Winestro\CronManagerInterface;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\TaskManagerInterface;
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
        $this->logManager->setTaskId(uniqid());
        try {
            $this->logManager->logCron("[cron run execute]");
            $this->logManager->logCron('[cron] tasks ' . implode(',', $taskIds));
            foreach ($taskIds as $taskId) {
                $this->logManager->logCron('[cron] run task ' . $taskId);
                $this->taskManager->getTask($taskId)->execute();
                $this->logManager->logCron('[cron] success task ' . $taskId);
            }
            $this->logManager->logCron('[cron success]' . $taskId);
        } catch (\Exception $e) {
            $this->logManager->logException($e);
            $this->logManager->logCron('[cron] ' . $e->getMessage());
            $this->logManager->logCron('[cron failed]');
        }
    }
}
